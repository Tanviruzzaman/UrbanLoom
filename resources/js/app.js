/**
 * Product gallery: clicking a thumbnail swaps the main image.
 * Progressive enhancement — without JS the thumbnails are still visible.
 */
document.addEventListener('click', (event) => {
    const thumb = event.target.closest('[data-gallery-thumbs] button');

    if (!thumb) {
        return;
    }

    const main = document.getElementById('gallery-main');
    const full = thumb.dataset.full;

    if (main && full) {
        main.src = full;

        thumb
            .closest('[data-gallery-thumbs]')
            .querySelectorAll('button')
            .forEach((button) => button.classList.remove('border-neutral-900'));

        thumb.classList.add('border-neutral-900');
    }
});

/**
 * Checkout: update the delivery charge and total as soon as a city is picked.
 *
 * Display only — CheckoutController recalculates the charge from the submitted
 * city on the server, so this can never change what the customer is billed.
 */
const formatBdt = (amount) => {
    const value = Number(amount) || 0;
    const decimals = Number.isInteger(value) ? 0 : 2;

    return '৳' + value.toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
};

const syncOrderSummary = () => {
    const city = document.getElementById('city');
    const summary = document.querySelector('[data-order-summary]');

    if (!city || !summary) {
        return;
    }

    const chargeCell = summary.querySelector('[data-delivery-charge]');
    const totalCell = summary.querySelector('[data-order-total]');
    const subtotal = Number(summary.dataset.subtotal) || 0;
    const selected = city.options[city.selectedIndex];
    const charge = selected ? selected.dataset.charge : undefined;

    if (charge === undefined || charge === '') {
        chargeCell.innerHTML = '<span class="font-normal text-neutral-500">Select a city</span>';
        totalCell.textContent = formatBdt(subtotal) + '+';

        return;
    }

    chargeCell.textContent = formatBdt(charge);
    totalCell.textContent = formatBdt(subtotal + Number(charge));
};

document.addEventListener('change', (event) => {
    if (event.target.id === 'city') {
        syncOrderSummary();
    }
});

// Re-sync on load so a browser-restored selection matches the summary.
document.addEventListener('DOMContentLoaded', syncOrderSummary);
