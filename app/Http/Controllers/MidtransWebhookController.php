<?php

namespace App\Http\Controllers;

use App\Models\PhotoboothOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'string', 'max:50'],
            'status_code' => ['required', 'string', 'max:10'],
            'gross_amount' => ['required', 'string', 'regex:/^\d+(?:\.\d{1,2})?$/'],
            'signature_key' => ['required', 'string', 'size:128'],
            'transaction_status' => ['required', 'string', 'max:24'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'payment_type' => ['nullable', 'string', 'max:40'],
            'fraud_status' => ['nullable', 'string', 'max:24'],
            'merchant_id' => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Notifikasi pembayaran tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $notification = $validator->validated();
        $serverKey = config('services.midtrans.server_key');

        if (! is_string($serverKey) || $serverKey === '') {
            return response()->json(['message' => 'Webhook pembayaran belum dikonfigurasi.'], 503);
        }

        $expectedSignature = hash(
            'sha512',
            $notification['order_id'].$notification['status_code'].$notification['gross_amount'].$serverKey,
        );

        if (! hash_equals($expectedSignature, $notification['signature_key'])) {
            Log::warning('Midtrans webhook signature verification failed.', [
                'order_id' => $notification['order_id'],
            ]);

            return response()->json(['message' => 'Signature pembayaran tidak valid.'], 403);
        }

        $expectedMerchantId = config('services.midtrans.merchant_id');

        if (! is_string($expectedMerchantId) || $expectedMerchantId === '') {
            return response()->json(['message' => 'ID merchant pembayaran belum dikonfigurasi.'], 503);
        }

        if (! hash_equals($expectedMerchantId, $notification['merchant_id'])) {
            Log::warning('Midtrans webhook merchant verification failed.', [
                'order_id' => $notification['order_id'],
            ]);

            return response()->json(['message' => 'Merchant pembayaran tidak valid.'], 403);
        }

        $order = PhotoboothOrder::query()
            ->where('midtrans_order_id', $notification['order_id'])
            ->first();

        if ($order === null) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if (! $this->amountMatches((int) $order->amount, $notification['gross_amount'])) {
            Log::warning('Midtrans webhook amount did not match the order.', [
                'order_id' => $notification['order_id'],
            ]);

            return response()->json(['message' => 'Nominal pembayaran tidak sesuai.'], 403);
        }

        DB::transaction(function () use ($notification, $order): void {
            $lockedOrder = PhotoboothOrder::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $isPaid = $notification['status_code'] === '200'
                && ($notification['transaction_status'] === 'settlement'
                || (
                    $notification['transaction_status'] === 'capture'
                        && ($notification['fraud_status'] ?? null) === 'accept'
                ));

            if ($isPaid && $lockedOrder->payment_status !== 'paid') {
                $lockedOrder->forceFill([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                    'midtrans_payment_type' => $notification['payment_type'] ?? null,
                ])->save();

                return;
            }

            if (
                $lockedOrder->payment_status !== 'paid'
                && in_array($notification['transaction_status'], ['deny', 'cancel', 'expire'], true)
            ) {
                $lockedOrder->forceFill([
                    'payment_status' => 'failed',
                    'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                    'midtrans_payment_type' => $notification['payment_type'] ?? null,
                ])->save();
            }
        });

        return response()->json(['message' => 'Notifikasi pembayaran diterima.']);
    }

    private function amountMatches(int $expectedAmount, string $receivedAmount): bool
    {
        [$wholeAmount, $fractionalAmount] = array_pad(explode('.', $receivedAmount, 2), 2, '');

        return ltrim($wholeAmount, '0') === (string) $expectedAmount
            && trim($fractionalAmount, '0') === '';
    }
}
