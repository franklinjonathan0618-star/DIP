@extends('layouts.photobooth')

@section('title', 'Ruang Kilat — Kirim foto')

@section('content')
    <section class="checkout-page" data-checkout-page>
        <header class="checkout-heading">
            <p class="eyebrow"><span>03</span> KIRIM FOTO</p>
            <h1>Foto ke emailmu<span>.</span></h1>
            <p>Masukkan alamat email. Foto akan dikirim sebagai lampiran.</p>
        </header>

        <div class="checkout-grid">
            <section class="checkout-card">
                <h2>Kontak pemesan</h2>
                <form action="{{ route('photobooth.checkout.store') }}" method="POST" data-checkout-form>
                    @csrf
                    <div class="checkout-fields">
                        <label for="checkout-email">
                            Email
                            <input id="checkout-email" type="email" name="email" autocomplete="email" maxlength="255" required>
                        </label>
                        <label for="checkout-phone">
                            Nomor telepon
                            <input id="checkout-phone" type="tel" name="phone" autocomplete="tel" maxlength="24" placeholder="08xxxxxxxxxx" required>
                        </label>
                        <button class="checkout-submit" type="submit" data-checkout-submit>
                            Kirim foto ke email
                        </button>
                        <p class="checkout-message" data-checkout-message role="alert" aria-live="polite"></p>
                        <a class="checkout-order-link" data-checkout-order-link hidden>Buka status pengiriman</a>
                    </div>
                </form>
            </section>

            <aside class="checkout-card checkout-summary" aria-label="Informasi pengiriman foto">
                <h2>Pengiriman email</h2>
                <p class="payment-note">Pembayaran belum digunakan. Foto yang dipilih akan dilampirkan dalam email ke alamat yang kamu masukkan.</p>
                <p>File foto tetap disimpan secara privat untuk catatan pesanan. Jika pengiriman gagal, status pesanan menyediakan tombol untuk mencoba lagi.</p>
            </aside>
        </div>
    </section>
@endsection
