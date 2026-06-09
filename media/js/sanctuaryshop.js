// SanctuaryShop — shared frontend helpers

document.addEventListener('DOMContentLoaded', function () {
    // Flash "added to cart" feedback on add-to-cart forms
    document.querySelectorAll('form[action*="cart.add"]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.textContent = 'Added!';
                btn.classList.replace('btn-primary', 'btn-success');
            }
        });
    });
});
