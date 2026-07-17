(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var dialog = document.getElementById('quoteModal');
        if (!dialog) return;

        var closeBtn = dialog.querySelector('[data-quote-close]');
        var refToggle = dialog.querySelector('[data-quote-reference-toggle]');
        var refField = dialog.querySelector('[data-quote-reference-field]');

        document.querySelectorAll('[data-quote-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                dialog.showModal();
            });
        });

        if (refToggle && refField) {
            refToggle.addEventListener('change', function () {
                refField.classList.toggle('hidden', !refToggle.checked);
            });
        }

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
        // renvoyé vers la page boutique (voir OrderController::storeGeneric()).
        if (dialog.hasAttribute('data-quote-reopen')) {
            dialog.showModal();
        }
    });
})();
