const STORAGE_KEY = 'ruang-kilat:photos';
const LAYOUT_STORAGE_KEY = 'ruang-kilat:layout';
const MAX_PHOTOS = 3;

export function readPhotos() {
    try {
        const storedValue = sessionStorage.getItem(STORAGE_KEY);

        if (!storedValue) {
            return [];
        }

        const parsedValue = JSON.parse(storedValue);

        if (
            !parsedValue
            || parsedValue.version !== 1
            || !Array.isArray(parsedValue.photos)
            || parsedValue.photos.length > MAX_PHOTOS
            || parsedValue.photos.some((photo) => typeof photo !== 'string' || !photo.startsWith('data:image/jpeg;base64,'))
        ) {
            throw new Error('Data foto tidak valid. Ambil foto kembali dari studio.');
        }

        return parsedValue.photos;
    } catch (error) {
        if (error instanceof SyntaxError) {
            throw new Error('Data foto tidak dapat dibaca. Ambil foto kembali dari studio.');
        }

        if (error instanceof DOMException) {
            throw new Error('Penyimpanan browser tidak dapat diakses. Periksa pengaturan browser, lalu coba lagi.');
        }

        throw error;
    }
}

export function readLayout() {
    try {
        const layout = sessionStorage.getItem(LAYOUT_STORAGE_KEY);

        return layout === 'separate' ? 'separate' : 'strip';
    } catch (error) {
        if (error instanceof DOMException) {
            throw new Error('Penyimpanan browser tidak dapat diakses. Periksa pengaturan browser, lalu coba lagi.');
        }

        throw error;
    }
}

export function saveLayout(layout) {
    if (layout !== 'strip' && layout !== 'separate') {
        throw new Error('Pilihan susunan foto tidak valid.');
    }

    try {
        sessionStorage.setItem(LAYOUT_STORAGE_KEY, layout);
    } catch (error) {
        if (error instanceof DOMException && error.name === 'QuotaExceededError') {
            throw new Error('Penyimpanan browser penuh. Hapus foto lalu coba lagi.');
        }

        throw new Error('Pilihan susunan tidak dapat disimpan di browser. Coba lagi.');
    }
}

export function savePhotos(photos) {
    if (photos.length > MAX_PHOTOS) {
        throw new Error('Maksimal tiga foto dalam satu sesi.');
    }

    try {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
            version: 1,
            photos,
        }));
    } catch (error) {
        if (error instanceof DOMException && error.name === 'QuotaExceededError') {
            throw new Error('Penyimpanan browser penuh. Hapus satu foto lalu coba lagi.');
        }

        throw new Error('Foto tidak dapat disimpan di browser. Periksa pengaturan penyimpanan, lalu coba lagi.');
    }
}
