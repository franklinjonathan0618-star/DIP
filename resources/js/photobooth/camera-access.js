function cameraErrorMessage(error) {
    if (error.name === 'NotAllowedError' || error.name === 'SecurityError') {
        return 'Izin kamera belum diberikan. Izinkan akses kamera di pengaturan browser, lalu coba lagi.';
    }

    if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
        return 'Kamera tidak ditemukan. Sambungkan kamera atau gunakan perangkat yang memiliki kamera.';
    }

    if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
        return 'Kamera sedang digunakan aplikasi lain. Tutup aplikasi tersebut, lalu coba lagi.';
    }

    if (error.name === 'OverconstrainedError') {
        return 'Kamera tidak mendukung pengaturan yang diminta. Coba pilih kamera lainnya.';
    }

    if (error.name === 'TypeError') {
        return 'Browser ini tidak mendukung akses kamera. Coba gunakan browser versi terbaru.';
    }

    return `Kamera tidak dapat diakses: ${error.message || 'terjadi kesalahan yang tidak diketahui.'}`;
}

export function createCameraAccess(video, onChange, onError) {
    let stream = null;
    let facingMode = 'user';

    function stop() {
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }

        video.pause();
        video.srcObject = null;
        video.hidden = true;
        onChange(false);
    }

    async function start() {
        if (!navigator.mediaDevices?.getUserMedia) {
            onChange(false);
            onError('Browser ini tidak mendukung akses kamera. Buka halaman melalui localhost atau HTTPS dan gunakan browser versi terbaru.');
            return;
        }

        try {
            const nextStream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    facingMode: { ideal: facingMode },
                    width: { ideal: 1280 },
                    height: { ideal: 960 },
                },
            });

            stream = nextStream;
            video.srcObject = stream;
            video.hidden = false;
            await video.play();

            stream.getVideoTracks().forEach((track) => {
                track.addEventListener('ended', () => {
                    if (stream?.getVideoTracks().includes(track)) {
                        stop();
                        onError('Kamera terputus. Periksa perangkat kamera, lalu aktifkan kembali.');
                    }
                }, { once: true });
            });

            onChange(true);
        } catch (error) {
            if (stream) {
                stop();
            }

            onChange(false);
            onError(cameraErrorMessage(error));
        }
    }

    async function flip() {
        if (!stream) {
            return;
        }

        facingMode = facingMode === 'user' ? 'environment' : 'user';
        stop();
        await start();
    }

    return {
        isActive: () => Boolean(stream),
        start,
        stop,
        flip,
    };
}
