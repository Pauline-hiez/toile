(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var dialog = document.getElementById('portfolioLightbox');
        if (!dialog) return;

        var img = dialog.querySelector('[data-lightbox-img]');
        var closeBtn = dialog.querySelector('[data-lightbox-close]');
        var prevBtn = dialog.querySelector('[data-lightbox-prev]');
        var nextBtn = dialog.querySelector('[data-lightbox-next]');
        var triggers = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox-open]'));
        var currentIndex = 0;

        if (triggers.length <= 1) {
            if (prevBtn) prevBtn.classList.add('hidden');
            if (nextBtn) nextBtn.classList.add('hidden');
        }

        function show(index) {
            currentIndex = (index + triggers.length) % triggers.length;
            var trigger = triggers[currentIndex];
            img.src = trigger.getAttribute('data-lightbox-src');
            img.alt = trigger.getAttribute('data-lightbox-alt') || '';
        }

        triggers.forEach(function (trigger, index) {
            trigger.addEventListener('click', function () {
                show(index);
                dialog.showModal();
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                show(currentIndex - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                show(currentIndex + 1);
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                dialog.close();
            });
        }

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                dialog.close();
            }
        });

        dialog.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') show(currentIndex - 1);
            if (e.key === 'ArrowRight') show(currentIndex + 1);
        });

        dialog.addEventListener('close', function () {
            img.src = '';
        });
    });
})();
