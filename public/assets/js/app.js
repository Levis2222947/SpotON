// SpotOn – kleine verbeteringen. De app werkt ook zonder JavaScript.
document.addEventListener('DOMContentLoaded', () => {
    // Bevestiging vragen bij formulieren met data-confirm (annuleren, verwijderen)
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // Scanpagina: bij het kiezen van een evenement direct de statistieken laden
    document.querySelectorAll('select[data-navigate]').forEach((select) => {
        select.addEventListener('change', () => {
            const value = select.value;
            window.location.href = select.dataset.navigate + (value ? '&event=' + encodeURIComponent(value) : '');
        });
    });

    // Live samenvatting bij het reserveren
    const quantity = document.querySelector('[data-quantity]');
    const summary = document.querySelector('[data-quantity-summary]');
    if (quantity && summary) {
        const update = () => {
            const n = parseInt(quantity.value, 10) || 0;
            summary.textContent = String(n);
        };
        quantity.addEventListener('input', update);
        update();
    }
});
