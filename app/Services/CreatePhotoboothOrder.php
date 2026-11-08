<?php

namespace App\Services;

use App\Models\PhotoboothOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CreatePhotoboothOrder
{
    /**
     * @param  array{email: string, phone: string, layout: string, photos: array<int, string>, composite_photo?: string}  $data
     */
    public function handle(array $data): PhotoboothOrder
    {
        $publicId = (string) Str::uuid();
        $midtransOrderId = 'RK-'.$publicId;
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($data, $publicId, $midtransOrderId, &$storedPaths): PhotoboothOrder {
                $order = PhotoboothOrder::query()->create([
                    'public_id' => $publicId,
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'layout' => $data['layout'],
                    'amount' => 0,
                    'payment_status' => 'not_required',
                    'midtrans_order_id' => $midtransOrderId,
                    'delivery_status' => 'pending',
                ]);

                foreach ($data['photos'] as $index => $dataUrl) {
                    $this->storePhoto($order, $dataUrl, 'original', $index + 1, $storedPaths);
                }

                if ($data['layout'] === 'strip' && isset($data['composite_photo'])) {
                    $this->storePhoto($order, $data['composite_photo'], 'strip', null, $storedPaths);
                }

                return $order;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);

            throw $exception;
        }
    }

    /**
     * @param  list<string>  $storedPaths
     */
    private function storePhoto(
        PhotoboothOrder $order,
        string $dataUrl,
        string $kind,
        ?int $photoNumber,
        array &$storedPaths,
    ): void {
        if (! preg_match('/\Adata:image\/jpeg;base64,([A-Za-z0-9+\/]*={0,2})\z/', $dataUrl, $matches)) {
            throw new RuntimeException('Salah satu foto tidak dapat disimpan. Ambil foto kembali.');
        }

        $contents = base64_decode($matches[1], true);

        if ($contents === false) {
            throw new RuntimeException('Salah satu foto tidak dapat disimpan. Ambil foto kembali.');
        }

        $filename = $photoNumber === null
            ? 'photobooth/'.$order->public_id.'/photostrip.jpg'
            : sprintf('photobooth/%s/foto-%02d.jpg', $order->public_id, $photoNumber);

        if (! Storage::disk('local')->put($filename, $contents)) {
            throw new RuntimeException('Foto tidak dapat disimpan. Silakan coba beberapa saat lagi.');
        }

        $storedPaths[] = $filename;

        $order->photos()->create([
            'kind' => $kind,
            'photo_number' => $photoNumber,
            'disk' => 'local',
            'path' => $filename,
            'mime_type' => 'image/jpeg',
            'size_bytes' => strlen($contents),
        ]);
    }
}
