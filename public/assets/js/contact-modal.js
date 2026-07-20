(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var dialog = document.getElementById('contactModal');
        if (!dialog) return;

        var closeBtn = dialog.querySelector('[data-contact-close]');

        document.querySelectorAll('[data-contact-open]').forEach(function (trigger) {
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

        // Rouvre automatiquement la modale si la page a été rechargée avec
        // ?contact=sent|error (voir ContactController::submit()).
        if (dialog.hasAttribute('data-contact-reopen')) {
            dialog.showModal();
        }
    });
})();
