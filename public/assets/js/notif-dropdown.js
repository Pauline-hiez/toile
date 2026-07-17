(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('[data-notif-toggle]');
        var menu = document.querySelector('[data-notif-menu]');
        if (!toggle || !menu) return;

        var badge = document.querySelector('[data-notif-badge]');
        var markAllBtn = menu.querySelector('[data-notif-markall]');
        var csrfInput = menu.querySelector('[data-notif-csrf]');

        function closeMenu() {
            menu.classList.remove('is-open');
        }

        function toggleMenu() {
            menu.classList.toggle('is-open');
        }

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            toggleMenu();
        });

        if (markAllBtn) {
            markAllBtn.addEventListener('click', function () {
                if (badge) badge.remove();
                menu.querySelectorAll('.site-header__notif-item--unread').forEach(function (item) {
                    item.classList.remove('site-header__notif-item--unread');
                });
                menu.querySelectorAll('.site-header__notif-dot').forEach(function (dot) {
                    dot.remove();
                });
                markAllBtn.remove();

                var body = new URLSearchParams();
                body.set('csrf_token', csrfInput ? csrfInput.value : '');

                fetch('/notifications/mark-read', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                });
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
