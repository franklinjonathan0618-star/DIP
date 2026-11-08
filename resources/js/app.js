import { initializeStudio } from './photobooth/studio.js';
import { initializeArrangement } from './photobooth/layout.js';
import { initializeCheckout } from './photobooth/checkout.js';

const studio = document.querySelector('[data-photobooth]');
const arrangement = document.querySelector('[data-arrangement]');
const checkoutForm = document.querySelector('[data-checkout-form]');

if (studio) {
    initializeStudio(studio);
}

if (arrangement) {
    initializeArrangement(arrangement);
}

if (checkoutForm) {
    initializeCheckout(checkoutForm);
}
