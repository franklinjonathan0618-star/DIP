<?php

namespace App\Mail;

use App\Models\PhotoboothOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PhotoboothPhotos extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PhotoboothOrder $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Foto photobooth kamu sudah siap',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.photobooth-photos',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $kind = $this->order->layout === 'strip' ? 'strip' : 'original';

        return $this->order->photos
            ->where('kind', $kind)
            ->sortBy('photo_number')
            ->map(fn ($photo): Attachment => Attachment::fromStorageDisk($photo->disk, $photo->path)
                ->as($photo->kind === 'strip'
                    ? 'ruang-kilat-photostrip.jpg'
                    : sprintf('ruang-kilat-foto-%02d.jpg', $photo->photo_number))
                ->withMime($photo->mime_type))
            ->values()
            ->all();
    }
}
