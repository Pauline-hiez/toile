(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var dialog = document.getElementById('authModal');
        if (!dialog) return;

        var panels = dialog.querySelectorAll('[data-auth-panel]');
        var closeBtn = dialog.querySelector('[data-auth-close]');

        function showPanel(name) {
            panels.forEach(function (panel) {
                panel.classList.toggle('hidden', panel.dataset.authPanel !== name);
            });
        }

        // Boutons du header (data-auth-open="login"|"register") : ouvrent
        // la modale sur le bon panneau. Le href réel (/login, /register)
        // reste en place pour la dégradation sans JS.
        document.querySelectorAll('[data-auth-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                showPanel(trigger.dataset.authOpen);
                dialog.showModal();
            });
        });

        // Liens "Pas encore de compte ?" / "Déjà un compte ?" à l'intérieur
        // de la modale : basculent de panneau sans la fermer.
        dialog.querySelectorAll('[data-auth-switch]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                showPanel(link.dataset.authSwitch);
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
    });
})();
