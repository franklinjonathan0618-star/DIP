@extends('layouts.photobooth')

@section('title', 'Ruang Kilat — Susun foto')

@section('content')
    <div
        class="arrangement-page"
        data-arrangement
        data-capture-url="{{ route('photobooth.capture') }}"
        data-checkout-url="{{ route('photobooth.checkout') }}"
    >
        <section class="intro arrangement-intro">
            <div>
                <p class="eyebrow"><span>02</span> YOUR PHOTOS, YOUR WAY</p>
                <h1>Susun <span>ceritamu.</span></h1>
                <p class="intro-copy">Pilih bagaimana foto-foto ini ingin kamu simpan.</p>
            </div>
            <div class="photo-count-badge"><span class="capture-badge-dot"></span><span data-layout-photo-count>0 foto</span></div>
        </section>

        <section class="arrangement-grid" aria-label="Susun hasil foto">
            <div class="arrangement-controls">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow"><span>01</span> PILIH SUSUNAN</p>
                        <h2>Jadi satu atau pisah?</h2>
                    </div>
                </div>

                <div class="layout-options" role="group" aria-label="Susunan foto">
                    <button class="layout-option is-selected" type="button" data-layout-option="strip" aria-pressed="true">
                        <span class="layout-option-art layout-strip-art" aria-hidden="true"><i></i><i></i><i></i></span>
                        <span class="layout-option-copy"><strong>Gabung jadi strip</strong><small>Semua foto dalam satu gambar panjang</small></span>
                        <span class="layout-option-check" aria-hidden="true">✓</span>
                    </button>
                    <button class="layout-option" type="button" data-layout-option="separate" aria-pressed="false">
                        <span class="layout-option-art layout-separate-art" aria-hidden="true"><i></i><i></i><i></i></span>
                        <span class="layout-option-copy"><strong>Satu per satu</strong><small>Simpan setiap foto sebagai gambar sendiri</small></span>
                        <span class="layout-option-check" aria-hidden="true">✓</span>
                    </button>
                </div>

                <div class="layout-divider"></div>
                <a class="back-button" href="{{ route('photobooth.capture') }}"><span aria-hidden="true">←</span> Kembali ke kamera</a>
            </div>

            <div class="result-panel">
                <div class="result-heading">
                    <div>
                        <p class="eyebrow"><span>02</span> PREVIEW HASIL</p>
                        <h2 data-result-title>Photostrip kamu</h2>
                    </div>
                    <span class="step-badge">LANGKAH 02 <i>·</i> EMAIL</span>
                </div>

                <div class="result-preview" data-result-preview>
                    <div class="strip-preview" data-strip-preview aria-label="Pratinjau photostrip"></div>
                    <div class="separate-preview" data-separate-preview hidden></div>
                    <p class="camera-error" data-layout-error role="alert" hidden></p>
                </div>

                <div class="result-actions">
                    <span class="result-note" data-result-note>Foto akan dikirim sebagai lampiran email.</span>
                    <a class="download-button checkout-next" data-checkout-link href="{{ route('photobooth.checkout') }}" hidden>
                        Selanjutnya · Kirim ke email
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
