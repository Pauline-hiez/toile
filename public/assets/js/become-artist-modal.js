(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var dialog = document.getElementById('becomeArtistModal');
        if (!dialog) return;

        var closeBtn = dialog.querySelector('[data-become-artist-close]');

        document.querySelectorAll('[data-become-artist-open]').forEach(function (trigger) {
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
        // renvoyé vers cette page (voir ArtistController::submitRequest()).
        if (dialog.hasAttribute('data-become-artist-reopen')) {
            dialog.showModal();
        }
    });
})();
