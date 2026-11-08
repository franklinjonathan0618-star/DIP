import { renderPhotostrip } from './photo-renderer.js';
import { readPhotos, readLayout, saveLayout } from './photo-storage.js';

export function initializeArrangement(page) {
    const captureUrl = page.dataset.captureUrl;
    const checkoutUrl = page.dataset.checkoutUrl;
    const photoCount = page.querySelector('[data-layout-photo-count]');
    const layoutError = page.querySelector('[data-layout-error]');
    const stripPreview = page.querySelector('[data-strip-preview]');
    const separatePreview = page.querySelector('[data-separate-preview]');
    const resultTitle = page.querySelector('[data-result-title]');
    const resultNote = page.querySelector('[data-result-note]');
    const checkoutLink = page.querySelector('[data-checkout-link]');
    const layoutOptions = [...page.querySelectorAll('[data-layout-option]')];

    let photos = [];

    function showError(message) {
        layoutError.textContent = message;
        layoutError.hidden = false;
        checkoutLink.hidden = true;
    }

    async function renderStrip() {
        try {
            const strip = await renderPhotostrip(photos);
            const preview = document.createElement('img');
            preview.src = strip;
            preview.alt = 'Pratinjau photostrip gabungan';

            stripPreview.replaceChildren(preview);
            stripPreview.hidden = false;
            separatePreview.hidden = true;
            resultTitle.textContent = 'Photostrip kamu';
            resultNote.textContent = 'Photostrip akan dikirim sebagai lampiran email.';
            checkoutLink.href = checkoutUrl;
            checkoutLink.hidden = false;
        } catch (error) {
            showError(error.message);
        }
    }

    function renderSeparatePhotos() {
        separatePreview.replaceChildren();

        photos.forEach((photo, index) => {
            const card = document.createElement('article');
            card.className = 'separate-photo-card';

            const image = document.createElement('img');
            image.src = photo;
            image.alt = `Foto ${index + 1}`;

            const label = document.createElement('span');
            label.className = 'separate-photo-label';
            label.textContent = `Foto ${index + 1}`;

            card.append(image, label);
            separatePreview.append(card);
        });

        stripPreview.hidden = true;
        separatePreview.hidden = false;
        resultTitle.textContent = 'Foto satu per satu';
        resultNote.textContent = 'Foto-foto akan dikirim sebagai lampiran email.';
        checkoutLink.href = checkoutUrl;
        checkoutLink.hidden = false;
    }

    function selectLayout(selectedLayout) {
        layoutOptions.forEach((button) => {
            const isSelected = button.dataset.layoutOption === selectedLayout;
            button.classList.toggle('is-selected', isSelected);
            button.setAttribute('aria-pressed', String(isSelected));
        });

        layoutError.hidden = true;

        try {
            saveLayout(selectedLayout);
        } catch (error) {
            showError(error.message);
            return;
        }

        if (selectedLayout === 'strip') {
            renderStrip();
            return;
        }

        renderSeparatePhotos();
    }

    layoutOptions.forEach((button) => {
        button.addEventListener('click', () => selectLayout(button.dataset.layoutOption));
    });

    try {
        photos = readPhotos();

        if (photos.length === 0) {
            throw new Error('Belum ada foto untuk disusun. Ambil minimal satu foto di studio terlebih dahulu.');
        }

        photoCount.textContent = `${photos.length} foto`;
        selectLayout(readLayout());
    } catch (error) {
        photoCount.textContent = '0 foto';
        showError(error.message);
        stripPreview.hidden = true;
        layoutOptions.forEach((button) => {
            button.disabled = true;
        });

        const backLink = page.querySelector('.back-button');
        backLink.href = captureUrl;
    }
}
