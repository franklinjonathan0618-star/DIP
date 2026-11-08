const FILTERS = {
    natural: 'none',
    warm: 'sepia(.24) saturate(1.13) contrast(1.03)',
    mono: 'grayscale(1) contrast(1.08)',
    vintage: 'sepia(.42) saturate(.82) contrast(.93)',
    cool: 'saturate(.88) hue-rotate(10deg) brightness(1.04)',
};

function drawCover(context, source, x, y, width, height, filter) {
    const sourceWidth = source.videoWidth || source.width;
    const sourceHeight = source.videoHeight || source.height;
    const sourceRatio = sourceWidth / sourceHeight;
    const targetRatio = width / height;
    let cropX = 0;
    let cropY = 0;
    let cropWidth = sourceWidth;
    let cropHeight = sourceHeight;

    if (sourceRatio > targetRatio) {
        cropWidth = sourceHeight * targetRatio;
        cropX = (sourceWidth - cropWidth) / 2;
    } else {
        cropHeight = sourceWidth / targetRatio;
        cropY = (sourceHeight - cropHeight) / 2;
    }

    context.save();
    context.filter = FILTERS[filter];
    context.drawImage(source, cropX, cropY, cropWidth, cropHeight, x, y, width, height);
    context.restore();
}

export function renderPhoto(source, frame, filter) {
    const output = document.createElement('canvas');
    output.width = 1200;
    output.height = 900;

    const context = output.getContext('2d');

    if (!context) {
        throw new Error('Browser tidak dapat memproses foto.');
    }

    if (frame === 'polaroid') {
        context.fillStyle = '#fff8e9';
        context.fillRect(0, 0, output.width, output.height);
        drawCover(context, source, 42, 34, 1116, 735, filter);
        context.fillStyle = '#817866';
        context.textAlign = 'center';
        context.font = '600 19px sans-serif';
        context.fillText('MOMEN KECIL, CERITA BESAR  ·  RUANG KILAT', 600, 835);
    } else if (frame === 'berry') {
        context.fillStyle = '#f7c5ce';
        context.fillRect(0, 0, output.width, output.height);
        drawCover(context, source, 35, 35, 1130, 830, filter);
        context.strokeStyle = '#fff7ef';
        context.lineWidth = 5;
        context.strokeRect(45, 45, 1110, 810);
        context.fillStyle = '#fff7ef';
        context.textAlign = 'center';
        context.font = '600 25px sans-serif';
        context.fillText('♥', 600, 890);
    } else if (frame === 'midnight') {
        context.fillStyle = '#242b28';
        context.fillRect(0, 0, output.width, output.height);
        drawCover(context, source, 35, 35, 1130, 830, filter);
        context.strokeStyle = '#e7d3a6';
        context.lineWidth = 2;
        context.strokeRect(45, 45, 1110, 810);
        context.fillStyle = '#e7d3a6';
        context.textAlign = 'center';
        context.font = '600 17px sans-serif';
        context.fillText('R U A N G   K I L A T', 600, 891);
    } else {
        drawCover(context, source, 0, 0, output.width, output.height, filter);
    }

    return output.toDataURL('image/jpeg', 0.9);
}

function loadImage(source) {
    return new Promise((resolve, reject) => {
        const image = new Image();

        image.addEventListener('load', () => resolve(image), { once: true });
        image.addEventListener('error', () => reject(new Error('Salah satu foto tidak dapat dibuka. Ambil foto kembali.')), { once: true });
        image.src = source;
    });
}

export async function renderPhotostrip(photos) {
    const images = await Promise.all(photos.map(loadImage));
    const photoWidth = 820;
    const photoHeight = 615;
    const padding = 40;
    const gap = 18;
    const output = document.createElement('canvas');
    output.width = photoWidth + padding * 2;
    output.height = padding * 2 + photoHeight * images.length + gap * (images.length - 1);

    const context = output.getContext('2d');

    if (!context) {
        throw new Error('Browser tidak dapat menyusun photostrip.');
    }

    context.fillStyle = '#fff8e9';
    context.fillRect(0, 0, output.width, output.height);

    images.forEach((image, index) => {
        context.drawImage(image, padding, padding + index * (photoHeight + gap), photoWidth, photoHeight);
    });

    return output.toDataURL('image/jpeg', 0.92);
}
