<?php

namespace App\Services;

use App\Mail\PhotoboothPhotos;
use App\Models\PhotoboothOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SendPhotoboothPhotos
{
    public function handle(PhotoboothOrder $order): void
    {
        $mailer = config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->markFailed($order);

            throw new PhotoDeliveryException(
                'Pengiriman email belum diaktifkan. Atur MAIL_MAILER dan SMTP di file .env, lalu coba kirim ulang.',
            );
        }

        $order->loadMissing('photos');

        $kind = $order->layout === 'strip' ? 'strip' : 'original';

        if ($order->photos->where('kind', $kind)->isEmpty()) {
            $this->markFailed($order);

            throw new PhotoDeliveryException('File foto pesanan tidak ditemukan. Silakan buat pesanan baru.');
        }

        try {
            Mail::to($order->email)->send(new PhotoboothPhotos($order));
        } catch (TransportExceptionInterface $exception) {
            $this->markFailed($order);

            Log::warning('Photobooth photo email could not be sent.', [
                'order_id' => $order->public_id,
                'exception' => $exception->getMessage(),
            ]);

            throw new PhotoDeliveryException(
                'Email belum berhasil dikirim. Periksa konfigurasi SMTP, lalu coba lagi.',
                previous: $exception,
            );
        }

        $order->forceFill([
            'delivery_status' => 'sent',
        ])->save();
    }

    private function markFailed(PhotoboothOrder $order): void
    {
        $order->forceFill([
            'delivery_status' => 'failed',
        ])->save();
    }
}
