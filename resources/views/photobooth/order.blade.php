@extends('layouts.photobooth')

@section('title', 'Ruang Kilat — Status email')

@section('content')
    <section class="order-page">
        <header class="order-heading">
            <p class="eyebrow"><span>04</span> STATUS EMAIL</p>
            <h1>Pesanan kamu<span>.</span></h1>
            <p>Nomor pesanan: {{ $order->public_id }}</p>
        </header>

        <section class="order-card">
            @if (session('email_sent') || $order->delivery_status === 'sent')
                <span class="order-status is-sent">Email berhasil dikirim</span>
                <h2>Cek inbox kamu.</h2>
                <p>Foto dikirim ke alamat email yang kamu masukkan. Jika belum terlihat, periksa folder spam atau promosi.</p>
            @elseif (session('email_error') || $order->delivery_status === 'failed')
                <span class="order-status is-failed">Email belum terkirim</span>
                <h2>Pengiriman foto gagal.</h2>
                <p>{{ session('email_error', 'Periksa konfigurasi email aplikasi, lalu coba kirim ulang.') }}</p>
                <form action="{{ route('photobooth.orders.resend', $order->public_id) }}" method="POST">
                    @csrf
                    <button class="email-resend-button" type="submit">Coba kirim ulang</button>
                </form>
            @else
                <span class="order-status">Menyiapkan email</span>
                <h2>Pesanan sedang diproses.</h2>
                <p>Periksa kembali status email sebentar lagi.</p>
            @endif

            <a class="order-back-link" href="{{ route('photobooth.capture') }}">Kembali ke studio</a>
        </section>
    </section>
@endsection
