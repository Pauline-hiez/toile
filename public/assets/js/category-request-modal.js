(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var dialog = document.getElementById('categoryRequestModal');
        if (!dialog) return;

        var closeBtn = dialog.querySelector('[data-category-request-close]');
        var typeSelect = dialog.querySelector('[data-category-request-type]');
        var imageField = dialog.querySelector('[data-category-request-image]');

        function toggleImageField() {
            if (!typeSelect || !imageField) return;
            imageField.style.display = typeSelect.value === 'style' ? '' : 'none';
        }

        if (typeSelect) {
            typeSelect.addEventListener('change', toggleImageField);
            toggleImageField();
        }

        document.querySelectorAll('[data-category-request-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                dialog.showModal();
            });
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                dialog.close();
            });
        }

        // Ferme la modale si on clique sur le fond (backdrop).
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                dialog.close();
            }
        });

        // Rouvre automatiquement la modale si une erreur de validation a
        // renvoyé vers cette page (voir ShopController::submitCategoryRequest()).
        if (dialog.hasAttribute('data-category-request-reopen')) {
            dialog.showModal();
        }
    });
})();
