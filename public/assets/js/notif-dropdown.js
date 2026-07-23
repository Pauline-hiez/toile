(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('[data-notif-toggle]');
        var menu = document.querySelector('[data-notif-menu]');
        if (!toggle || !menu) return;

        var badge = document.querySelector('[data-notif-badge]');
        var markAllBtn = menu.querySelector('[data-notif-markall]');
        var csrfInput = menu.querySelector('[data-notif-csrf]');
        var hasMarked = false;

        // Marque toutes les notifications comme lues : retire la pastille,
        // enlève l'état "non lu" des éléments et prévient le serveur.
        // Idempotent (une seule fois par chargement de page) pour éviter des
        // requêtes répétées à chaque ouverture du menu.
        function markAllRead() {
            if (hasMarked) return;
            hasMarked = true;

            if (badge) badge.remove();
            menu.querySelectorAll('.site-header__notif-item--unread').forEach(function (item) {
                item.classList.remove('site-header__notif-item--unread');
            });
            menu.querySelectorAll('.site-header__notif-dot').forEach(function (dot) {
                dot.remove();
            });
            if (markAllBtn) markAllBtn.remove();

            var body = new URLSearchParams();
            body.set('csrf_token', csrfInput ? csrfInput.value : '');

            fetch('/notifications/mark-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
            });
        }

        function closeMenu() {
            menu.classList.remove('is-open');
        }

        function openMenu() {
            menu.classList.add('is-open');
            // Ouvrir le panneau vaut lecture : la pastille disparaît aussitôt
            // (comportement attendu quand on clique sur la cloche). Ne fait
            // rien s'il n'y avait aucune notification non lue.
            if (badge) {
                markAllRead();
            }
        }

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            if (menu.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        // Le bouton "Tout marquer comme lu" reste disponible (redondant une
        // fois le menu ouvert, mais sans effet de bord grâce au garde-fou).
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function () {
                markAllRead();
            });
        }

        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target) && !toggle.contains(e.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMenu();
            }
        });
    });
})();
