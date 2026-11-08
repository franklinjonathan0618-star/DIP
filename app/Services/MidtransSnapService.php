<?php

namespace App\Services;

use App\Models\PhotoboothOrder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransSnapService
{
    /**
     * @return array{token: string, redirect_url: string}
     */
    public function createTransaction(PhotoboothOrder $order): array
    {
        $serverKey = config('services.midtrans.server_key');

        if (! is_string($serverKey) || $serverKey === '') {
            throw new MidtransGatewayException('Pembayaran belum dikonfigurasi. Hubungi pengelola photobooth.');
        }

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withBasicAuth($serverKey, '')
                ->connectTimeout(3)
                ->timeout(15)
                ->post($baseUrl.'/snap/v1/transactions', [
                    'transaction_details' => [
                        'order_id' => $order->midtrans_order_id,
                        'gross_amount' => $order->amount,
                    ],
                    'item_details' => [
                        [
                            'id' => 'photobooth-photo',
                            'price' => $order->amount,
                            'quantity' => 1,
                            'name' => 'Foto photobooth',
                        ],
                    ],
                    'customer_details' => [
                        'email' => $order->email,
                        'phone' => $order->phone,
                    ],
                    'enabled_payments' => ['qris'],
                    'callbacks' => [
                        'finish' => route('photobooth.orders.show', $order->public_id),
                    ],
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Midtrans Snap connection failed.', [
                'order_id' => $order->midtrans_order_id,
                'exception' => $exception->getMessage(),
            ]);

            throw new MidtransGatewayException(
                'Layanan pembayaran tidak dapat dihubungi. Silakan coba lagi.',
                previous: $exception,
            );
        }

        if (! $response->successful()) {
            Log::warning('Midtrans Snap rejected a transaction request.', [
                'order_id' => $order->midtrans_order_id,
                'http_status' => $response->status(),
            ]);

            throw new MidtransGatewayException('Pembayaran belum dapat dibuat. Silakan coba lagi.');
        }

        $token = $response->json('token');
        $redirectUrl = $response->json('redirect_url');
        $redirectHost = is_string($redirectUrl) ? parse_url($redirectUrl, PHP_URL_HOST) : null;
        $expectedHost = config('services.midtrans.is_production')
            ? 'app.midtrans.com'
            : 'app.sandbox.midtrans.com';

        if (
            ! is_string($token)
            || $token === ''
            || ! is_string($redirectUrl)
            || parse_url($redirectUrl, PHP_URL_SCHEME) !== 'https'
            || $redirectHost !== $expectedHost
        ) {
            Log::warning('Midtrans Snap returned an invalid transaction response.', [
                'order_id' => $order->midtrans_order_id,
            ]);

            throw new MidtransGatewayException('Respons pembayaran tidak valid. Silakan coba lagi.');
        }

        return [
            'token' => $token,
            'redirect_url' => $redirectUrl,
        ];
    }
}
