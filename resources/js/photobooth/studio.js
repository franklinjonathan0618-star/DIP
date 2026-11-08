import { createCameraAccess } from "./camera-access.js";
import { renderPhoto } from "./photo-renderer.js";
import { readPhotos, savePhotos } from "./photo-storage.js";

const MAX_PHOTOS = 3;

export function initializeStudio(studio) {
    const video = studio.querySelector("[data-camera-feed]");
    const stage = studio.querySelector(".stage");
    const placeholder = studio.querySelector("[data-camera-placeholder]");
    const capturedImage = studio.querySelector("[data-captured-image]");
    const showCameraButton = studio.querySelector("[data-show-camera]");
    const cameraButton = studio.querySelector("[data-start-camera]");
    const cameraButtonLabel = studio.querySelector("[data-start-label]");
    const captureButton = studio.querySelector("[data-capture]");
    const flipButton = studio.querySelector("[data-flip-camera]");
    const countdown = studio.querySelector("[data-countdown]");
    const cameraLabel = studio.querySelector("[data-camera-label]");
    const statusText = studio.querySelector("[data-status-text]");
    const statusIndicator = studio.querySelector("[data-status-indicator]");
    const liveLabel = studio.querySelector(".live-label");
    const cameraError = studio.querySelector("[data-camera-error]");
    const photoCount = studio.querySelector("[data-photo-count]");
    const photoLabel = studio.querySelector("[data-photo-label]");
    const captureBadge = studio.querySelector("[data-capture-badge]");
    const captureHint = studio.querySelector("[data-capture-hint]");
    const nextButton = studio.querySelector("[data-next]");
    const photoStrip = studio.querySelector("[data-photo-strip]");

    let photos = [];
    let isCountingDown = false;
    let isShowingCapturePreview = true;
    let currentFrame = "polaroid";
    let currentFilter = "natural";

    function showCameraError(message) {
        cameraError.textContent = message;
        cameraError.hidden = false;
    }

    function clearCameraError() {
        cameraError.textContent = "";
        cameraError.hidden = true;
    }

    function renderLatestPhoto() {
        const latestPhoto = photos.at(-1);
        const showCapturePreview = Boolean(
            latestPhoto && isShowingCapturePreview,
        );
        capturedImage.hidden = !showCapturePreview;
        capturedImage.src = latestPhoto || "";
        showCameraButton.hidden =
            !showCapturePreview || !cameraAccess.isActive();
        stage.classList.toggle("is-captured", showCapturePreview);
        placeholder.hidden = Boolean(
            showCapturePreview || cameraAccess.isActive(),
        );
        video.hidden = !cameraAccess.isActive() || showCapturePreview;
    }

    function renderPhotoThumbnails() {
        photoStrip.replaceChildren();
        photoStrip.hidden = photos.length === 0;

        photos.forEach((photo, index) => {
            const thumbnail = document.createElement("div");
            thumbnail.className = "photo-thumbnail";

            const image = document.createElement("img");
            image.src = photo;
            image.alt = `Foto ${index + 1}`;

            const label = document.createElement("span");
            label.className = "photo-thumbnail-label";
            label.textContent = `FOTO ${String(index + 1).padStart(2, "0")}`;

            const removeButton = document.createElement("button");
            removeButton.className = "remove-photo";
            removeButton.type = "button";
            removeButton.setAttribute("aria-label", `Hapus foto ${index + 1}`);
            removeButton.textContent = "×";
            removeButton.addEventListener("click", () => removePhoto(index));

            thumbnail.append(image, label, removeButton);
            photoStrip.append(thumbnail);
        });
    }

    function updateCaptureControls() {
        const isCameraActive = cameraAccess.isActive();
        const hasReachedPhotoLimit = photos.length >= MAX_PHOTOS;

        statusText.textContent = isCameraActive
            ? "Kamera aktif"
            : "Kamera belum aktif";
        statusIndicator.classList.toggle("is-live", isCameraActive);
        liveLabel.classList.toggle("is-live", isCameraActive);
        cameraLabel.textContent = isCameraActive
            ? "KAMERA SIAP"
            : "SIAP MEMOTRET";
        cameraButtonLabel.textContent = isCameraActive
            ? "Matikan kamera"
            : "Aktifkan kamera";
        captureButton.disabled =
            !isCameraActive || hasReachedPhotoLimit || isCountingDown;
        flipButton.disabled = !isCameraActive || isCountingDown;
        photoCount.textContent = String(photos.length);
        photoLabel.textContent = "dari 3 foto";
        captureBadge.classList.toggle("has-photos", photos.length > 0);
        if (hasReachedPhotoLimit) {
            captureHint.textContent = "Maksimal foto sudah tercapai";
        } else if (photos.length === 0) {
            captureHint.textContent = "Ambil foto pertamamu";
        } else {
            captureHint.textContent = "Kamu bisa ambil foto lagi";
        }
        nextButton.setAttribute("aria-disabled", String(photos.length === 0));
    }

    function removePhoto(index) {
        const updatedPhotos = photos.filter(
            (_, photoIndex) => photoIndex !== index,
        );

        try {
            savePhotos(updatedPhotos);
            photos = updatedPhotos;
            renderPhotoThumbnails();
            renderLatestPhoto();
            updateCaptureControls();
            clearCameraError();
        } catch (error) {
            showCameraError(error.message);
        }
    }

    async function takePhoto() {
        if (
            !cameraAccess.isActive() ||
            isCountingDown ||
            photos.length >= MAX_PHOTOS
        ) {
            return;
        }

        clearCameraError();
        isCountingDown = true;
        isShowingCapturePreview = false;
        capturedImage.hidden = true;
        showCameraButton.hidden = true;
        video.hidden = false;
        stage.classList.remove("is-captured");
        countdown.hidden = false;
        updateCaptureControls();

        try {
            for (let count = 3; count > 0; count -= 1) {
                countdown.textContent = count;
                await new Promise((resolve) => window.setTimeout(resolve, 750));
            }

            if (!cameraAccess.isActive()) {
                throw new Error(
                    "Kamera berhenti sebelum foto diambil. Aktifkan kembali kamera, lalu coba lagi.",
                );
            }

            if (
                video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA ||
                !video.videoWidth ||
                !video.videoHeight
            ) {
                throw new Error(
                    "Kamera masih menyiapkan gambar. Tunggu sebentar, lalu coba lagi.",
                );
            }

            const photo = renderPhoto(video, currentFrame, currentFilter);
            const updatedPhotos = [...photos, photo];
            savePhotos(updatedPhotos);
            photos = updatedPhotos;
            isShowingCapturePreview = true;
            renderPhotoThumbnails();
            renderLatestPhoto();
        } catch (error) {
            showCameraError(
                error.message || "Foto tidak dapat diambil. Silakan coba lagi.",
            );
        } finally {
            isCountingDown = false;
            countdown.hidden = true;
            countdown.textContent = "";
            updateCaptureControls();
        }
    }

    const cameraAccess = createCameraAccess(
        video,
        (isActive) => {
            cameraButton.disabled = false;

            if (isActive) {
                isShowingCapturePreview = false;
                clearCameraError();
            } else if (photos.length > 0) {
                isShowingCapturePreview = true;
            }

            renderLatestPhoto();
            updateCaptureControls();
        },
        showCameraError,
    );

    function selectFrame(frame) {
        currentFrame = frame;
        stage.dataset.frame = frame;

        studio.querySelectorAll("[data-frame-option]").forEach((button) => {
            const isSelected = button.dataset.frameOption === frame;
            button.classList.toggle("is-selected", isSelected);
            button.setAttribute("aria-pressed", String(isSelected));
        });
    }

    function selectFilter(filter) {
        currentFilter = filter;
        stage.dataset.filter = filter;

        studio.querySelectorAll("[data-filter-option]").forEach((button) => {
            const isSelected = button.dataset.filterOption === filter;
            button.classList.toggle("is-selected", isSelected);
            button.setAttribute("aria-pressed", String(isSelected));
        });
    }

    cameraButton.addEventListener("click", async () => {
        clearCameraError();
        cameraButton.disabled = true;

        if (cameraAccess.isActive()) {
            cameraAccess.stop();
            cameraButton.disabled = false;
            renderLatestPhoto();
            return;
        }

        cameraButtonLabel.textContent = "Meminta izin...";
        await cameraAccess.start();
    });

    captureButton.addEventListener("click", takePhoto);
    showCameraButton.addEventListener("click", () => {
        isShowingCapturePreview = false;
        renderLatestPhoto();
    });
    flipButton.addEventListener("click", async () => {
        clearCameraError();
        flipButton.disabled = true;
        await cameraAccess.flip();
    });

    studio.querySelectorAll("[data-frame-option]").forEach((button) => {
        button.addEventListener("click", () =>
            selectFrame(button.dataset.frameOption),
        );
    });

    studio.querySelectorAll("[data-filter-option]").forEach((button) => {
        button.addEventListener("click", () =>
            selectFilter(button.dataset.filterOption),
        );
    });

    nextButton.addEventListener("click", (event) => {
        if (photos.length === 0) {
            event.preventDefault();
            return;
        }

        cameraAccess.stop();
    });

    window.addEventListener("pagehide", () => cameraAccess.stop(), {
        once: true,
    });

    try {
        photos = readPhotos();
        renderPhotoThumbnails();
        renderLatestPhoto();
    } catch (error) {
        showCameraError(error.message);
    }

    updateCaptureControls();
}
