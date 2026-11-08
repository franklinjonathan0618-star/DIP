<?php

namespace Tests\Feature;

use App\Mail\PhotoboothPhotos;
use App\Models\PhotoboothOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoboothEmailDeliveryTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const JPEG_DATA_URL = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAH/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/AKf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/AKf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/AKf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAwE/AKf/2Q==';

    public function test_checkout_form_explains_email_delivery_without_payment(): void
    {
        $response = $this->get(route('photobooth.checkout'));

        $response->assertOk()
            ->assertViewIs('photobooth.checkout')
            ->assertSeeText('Foto akan dikirim sebagai lampiran.')
            ->assertSeeText('Pembayaran belum digunakan.')
            ->assertDontSeeText('Midtrans');
    }

    public function test_order_saves_photos_and_sends_the_selected_photostrip_by_email(): void
    {
        Storage::fake('local');
        Mail::fake();
        config(['mail.default' => 'smtp']);

        $response = $this->postJson(route('photobooth.checkout.store'), $this->validPayload());

        $response->assertCreated()
            ->assertJsonStructure(['order_url']);

        $order = PhotoboothOrder::query()->with('photos')->sole();

        $this->assertSame('not_required', $order->payment_status);
        $this->assertSame('sent', $order->delivery_status);
        $this->assertSame(0, $order->amount);
        $this->assertCount(2, $order->photos);

        foreach ($order->photos as $photo) {
            $this->assertSame('local', $photo->disk);
            Storage::disk('local')->assertExists($photo->path);
        }

        Mail::assertSent(PhotoboothPhotos::class, function (PhotoboothPhotos $mail) use ($order): bool {
            $strip = $order->photos->firstWhere('kind', 'strip');

            return $strip !== null
                && $mail->hasTo($order->email)
                && count($mail->attachments()) === 1
                && $mail->hasAttachment(
                    Attachment::fromStorageDisk('local', $strip->path)
                        ->as('ruang-kilat-photostrip.jpg')
                        ->withMime('image/jpeg'),
                );
        });
    }

    public function test_separate_layout_attaches_each_original_photo(): void
    {
        Storage::fake('local');
        Mail::fake();
        config(['mail.default' => 'smtp']);
        $payload = $this->validPayload();
        $payload['layout'] = 'separate';
        $payload['photos'] = array_fill(0, 3, self::JPEG_DATA_URL);
        unset($payload['composite_photo']);

        $this->postJson(route('photobooth.checkout.store'), $payload)->assertCreated();

        $order = PhotoboothOrder::query()->with('photos')->sole();

        $this->assertCount(3, $order->photos);
        Mail::assertSent(PhotoboothPhotos::class, fn (PhotoboothPhotos $mail): bool => count($mail->attachments()) === 3);
    }

    public function test_mail_not_configured_returns_an_error_and_keeps_retryable_order(): void
    {
        Storage::fake('local');
        Mail::fake();
        config(['mail.default' => 'log']);

        $response = $this->postJson(route('photobooth.checkout.store'), $this->validPayload());

        $response->assertStatus(502)
            ->assertJsonPath('message', 'Pengiriman email belum diaktifkan. Atur MAIL_MAILER dan SMTP di file .env, lalu coba kirim ulang.')
            ->assertJsonStructure(['order_url']);

        $order = PhotoboothOrder::query()->sole();

        $this->assertSame('failed', $order->delivery_status);
        Mail::assertNothingOutgoing();
    }

    public function test_failed_email_can_be_retried_after_smtp_is_configured(): void
    {
        Storage::fake('local');
        Mail::fake();
        config(['mail.default' => 'log']);

        $createResponse = $this->postJson(route('photobooth.checkout.store'), $this->validPayload());
        $order = PhotoboothOrder::query()->sole();

        config(['mail.default' => 'smtp']);

        $response = $this->post(route('photobooth.orders.resend', $order->public_id));

        $response->assertRedirect(route('photobooth.orders.show', $order->public_id));
        $this->assertSame('sent', $order->fresh()->delivery_status);
        Mail::assertSent(PhotoboothPhotos::class);
        $this->assertNotEmpty($createResponse->json('order_url'));
    }

    public function test_invalid_photo_is_rejected_without_saving_an_order_or_sending_email(): void
    {
        Storage::fake('local');
        Mail::fake();
        config(['mail.default' => 'smtp']);
        $payload = $this->validPayload();
        $payload['photos'] = ['data:image/png;base64,ZmFrZQ=='];

        $response = $this->postJson(route('photobooth.checkout.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['photos.0']);

        $this->assertDatabaseCount('photobooth_orders', 0);
        Mail::assertNothingOutgoing();
    }

    public function test_order_status_shows_success_without_midtrans_or_download_controls(): void
    {
        Storage::fake('local');
        Mail::fake();
        config(['mail.default' => 'smtp']);
        $this->postJson(route('photobooth.checkout.store'), $this->validPayload())->assertCreated();
        $order = PhotoboothOrder::query()->sole();

        $response = $this->get(route('photobooth.orders.show', $order->public_id));

        $response->assertOk()
            ->assertSeeText('Email berhasil dikirim')
            ->assertSeeText('Cek inbox kamu.')
            ->assertDontSeeText('Pembayaran')
            ->assertDontSeeText('Unduh photostrip');
    }

    /**
     * @return array{email: string, phone: string, layout: string, photos: array<int, string>, composite_photo: string}
     */
    private function validPayload(): array
    {
        return [
            'email' => 'customer@example.com',
            'phone' => '081234567890',
            'layout' => 'strip',
            'photos' => [self::JPEG_DATA_URL],
            'composite_photo' => self::JPEG_DATA_URL,
        ];
    }
}
