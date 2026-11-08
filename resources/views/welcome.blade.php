@extends('layouts.photobooth')

@section('title', 'Ruang Kilat — Studio foto')

@section('content')
    <div data-photobooth data-arrangement-url="{{ route('photobooth.arrangement') }}">
        <section class="intro">
            <div>
                <p class="eyebrow"><span>01</span> YOUR LITTLE PHOTO STUDIO</p>
                <h1>Momen kecil,<br><span>cerita besar.</span></h1>
                <p class="intro-copy">Atur gaya, pilih bingkai, lalu abadikan hari ini.</p>
            </div>
            <div class="intro-stamp" aria-hidden="true">
                <span>SMILE!</span>
                <svg viewBox="0 0 52 52" fill="none">
                    <circle cx="26" cy="26" r="20" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M18 29c1.8 3.3 4.5 5 8 5s6.2-1.7 8-5M19 21h.1M33 21h.1" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="m8 7 3 3m33 32 3 3M44 8l-3 3M11 41l-3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
        </section>

        <section class="studio-grid" aria-label="Studio photobooth">
            <div class="preview-card">
                <div class="preview-toolbar">
                    <div class="live-label"><span class="live-dot"></span><span data-camera-label>SIAP MEMOTRET</span></div>
                    <span class="preview-meta">PREVIEW <span>·</span> 4:3</span>
                </div>

                <div class="stage" data-frame="polaroid">
                    <video class="camera-feed" data-camera-feed autoplay playsinline muted hidden></video>
                    <img class="captured-image" data-captured-image alt="Hasil foto photobooth" hidden>
                    <button
                        class="preview-dismiss"
                        type="button"
                        data-show-camera
                        aria-label="Tutup pratinjau dan tampilkan kamera"
                        title="Kembali ke kamera"
                        hidden
                    >×</button>
                    <div class="camera-placeholder" data-camera-placeholder>
                        <div class="placeholder-art" aria-hidden="true">
                            <span class="sun-shape"></span>
                            <span class="hill-shape hill-one"></span>
                            <span class="hill-shape hill-two"></span>
                            <span class="placeholder-person"></span>
                            <span class="placeholder-sparkle sparkle-one">✳</span>
                            <span class="placeholder-sparkle sparkle-two">✦</span>
                        </div>
                        <div class="placeholder-message">
                            <span class="placeholder-kicker">READY WHEN YOU ARE</span>
                            <strong>Waktunya jadi<br>bintang utama.</strong>
                            <span class="placeholder-hint">Aktifkan kamera untuk memulai</span>
                        </div>
                    </div>
                    <span class="stage-corner corner-top-left" aria-hidden="true"></span>
                    <span class="stage-corner corner-top-right" aria-hidden="true"></span>
                    <span class="stage-corner corner-bottom-left" aria-hidden="true"></span>
                    <span class="stage-corner corner-bottom-right" aria-hidden="true"></span>
                    <div class="countdown" data-countdown hidden aria-live="assertive"></div>
                </div>

                <div class="preview-footer">
                    <div class="preview-camera-controls">
                        <button class="camera-button preview-camera-button" type="button" data-start-camera>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h9A2.5 2.5 0 0 1 17 7.5v7a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 3 14.5v-7Z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="11" r="2.5" stroke="currentColor" stroke-width="1.5"/><path d="m7 5 1-2h4l1 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <span data-start-label>Aktifkan kamera</span>
                        </button>
                        <div class="camera-status"><span class="status-indicator" data-status-indicator></span><span data-status-text>Kamera belum aktif</span></div>
                    </div>
                    <button class="text-button" type="button" data-flip-camera disabled aria-label="Ganti kamera">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3 7V4m0 0h3M3 4l4 4m10 5v3m0 0h-3m3 0-4-4M7 6.5a5 5 0 0 1 8.4 1.1M13 13.5a5 5 0 0 1-8.4-1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Balik kamera
                    </button>
                </div>
                <p class="camera-error" data-camera-error role="alert" hidden></p>
                <div class="photo-strip" data-photo-strip aria-label="Foto yang sudah diambil" hidden></div>
            </div>

            <aside class="control-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow"><span>02</span> MAKE IT YOURS</p>
                        <h2>Atur gayamu</h2>
                    </div>
                    <span class="step-badge">01 <i>/</i> 02</span>
                </div>

                <div class="control-section">
                    <div class="section-title"><span class="section-number">A</span><h3>Pilih bingkai</h3></div>
                    <div class="frame-options" role="group" aria-label="Pilihan bingkai">
                        <button class="frame-option is-selected" type="button" data-frame-option="polaroid" aria-pressed="true">
                            <span class="frame-swatch frame-swatch-polaroid"><i></i></span>
                            <span>Polaroid</span>
                        </button>
                        <button class="frame-option" type="button" data-frame-option="berry" aria-pressed="false">
                            <span class="frame-swatch frame-swatch-berry"><i></i></span>
                            <span>Berry pop</span>
                        </button>
                        <button class="frame-option" type="button" data-frame-option="midnight" aria-pressed="false">
                            <span class="frame-swatch frame-swatch-midnight"><i></i></span>
                            <span>Midnight</span>
                        </button>
                        <button class="frame-option" type="button" data-frame-option="none" aria-pressed="false">
                            <span class="frame-swatch frame-swatch-none"><i></i></span>
                            <span>Tanpa frame</span>
                        </button>
                    </div>
                </div>

                <div class="control-section filter-section">
                    <div class="section-title"><span class="section-number">B</span><h3>Pilih filter</h3><span class="optional-label">PILIH SATU</span></div>
                    <div class="filter-options" role="group" aria-label="Pilihan filter">
                        <button class="filter-option is-selected" type="button" data-filter-option="natural" aria-pressed="true">
                            <span class="filter-swatch filter-natural"><i></i></span><span>Original</span>
                        </button>
                        <button class="filter-option" type="button" data-filter-option="warm" aria-pressed="false">
                            <span class="filter-swatch filter-warm"><i></i></span><span>Sunset</span>
                        </button>
                        <button class="filter-option" type="button" data-filter-option="mono" aria-pressed="false">
                            <span class="filter-swatch filter-mono"><i></i></span><span>Mono</span>
                        </button>
                        <button class="filter-option" type="button" data-filter-option="vintage" aria-pressed="false">
                            <span class="filter-swatch filter-vintage"><i></i></span><span>Retro</span>
                        </button>
                        <button class="filter-option" type="button" data-filter-option="cool" aria-pressed="false">
                            <span class="filter-swatch filter-cool"><i></i></span><span>Dreamy</span>
                        </button>
                    </div>
                </div>

                <div class="panel-divider"></div>
                <div class="capture-actions">
                    <button class="shutter-button" type="button" data-capture disabled>
                        <span class="shutter-icon"><span></span></span>
                        <span class="shutter-label">Ambil foto</span>
                        <span class="shutter-arrow" aria-hidden="true">↗</span>
                    </button>
                </div>

                <div class="capture-badge" data-capture-badge aria-live="polite">
                    <span class="capture-badge-dot"></span>
                    <span><strong data-photo-count>0</strong> <span data-photo-label>dari 3 foto</span></span>
                    <span class="capture-badge-hint" data-capture-hint>Ambil foto pertamamu</span>
                </div>
                <a class="next-button" data-next href="{{ route('photobooth.arrangement') }}" aria-disabled="true">
                    Selanjutnya
                    <span aria-hidden="true">→</span>
                </a>
                <p class="privacy-note"><span aria-hidden="true">✦</span> Foto diproses langsung di perangkatmu.</p>
            </aside>
        </section>
    </div>
@endsection
