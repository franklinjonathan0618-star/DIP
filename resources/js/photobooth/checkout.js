import { renderPhotostrip } from './photo-renderer.js';
import { readPhotos, readLayout } from './photo-storage.js';

export function initializeCheckout(form) {
    const submitButton = form.querySelector('[data-checkout-submit]');
    const message = form.querySelector('[data-checkout-message]');
    const orderLink = form.querySelector('[data-checkout-order-link]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        message.textContent = '';
        orderLink.hidden = true;
        submitButton.disabled = true;
        submitButton.textContent = 'Mengirim foto...';

        try {
            const photos = readPhotos();

            if (photos.length === 0) {
                throw new Error('Foto tidak ditemukan. Kembali ke studio dan ambil foto terlebih dahulu.');
            }

            const layout = readLayout();
            const formData = new FormData(form);

            formData.append('layout', layout);
            photos.forEach((photo) => formData.append('photos[]', photo));

            if (layout === 'strip') {
                formData.append('composite_photo', await renderPhotostrip(photos));
            }

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: formData,
                credentials: 'same-origin',
            });
            const result = await response.json();

            if (response.ok && typeof result.order_url === 'string') {
                window.location.assign(result.order_url);
                return;
            }

            if (typeof result.order_url === 'string') {
                orderLink.href = result.order_url;
                orderLink.hidden = false;
            }

            const firstError = Object.values(result.errors || {}).flat()[0];
            message.textContent = firstError || result.message || 'Pesanan belum dapat diproses. Silakan coba lagi.';
        } catch (error) {
            message.textContent = error instanceof Error
                ? error.message
                : 'Pesanan belum dapat diproses. Silakan coba lagi.';
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Kirim foto ke email';
        }
    });
}
