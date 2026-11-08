<?php

namespace Tests\Feature;

use Tests\TestCase;

class PhotoboothPagesTest extends TestCase
{
    public function test_studio_shows_camera_controls_and_keeps_download_off_the_capture_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewIs('welcome')
            ->assertSee('data-start-camera')
            ->assertSee('data-capture')
            ->assertSee('data-show-camera')
            ->assertSee('Tutup pratinjau dan tampilkan kamera')
            ->assertSeeText('dari 3 foto')
            ->assertDontSeeText('Unduh foto');
    }

    public function test_arrangement_page_offers_combined_and_separate_photo_layouts(): void
    {
        $response = $this->get('/susun-foto');

        $response->assertOk()
            ->assertViewIs('photobooth.arrangement')
            ->assertSeeText('Gabung jadi strip')
            ->assertSeeText('Satu per satu')
            ->assertDontSee('data-download-strip')
            ->assertDontSeeText('Unduh photostrip');
    }
}
