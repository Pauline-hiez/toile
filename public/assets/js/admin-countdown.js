(function () {
    'use strict';

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function formatCountdown(diffMs) {
        if (diffMs <= 0) {
            return '00J : 00H : 00MIN : 00S';
        }
        var totalSeconds = Math.floor(diffMs / 1000);
        var days = Math.floor(totalSeconds / (60 * 60 * 24));
        var hours = Math.floor((totalSeconds % (60 * 60 * 24)) / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;
        return pad(days) + 'J : ' + pad(hours) + 'H : ' + pad(minutes) + 'MIN : ' + pad(seconds) + 'S';
    }

    function tick(el, targetTime) {
        el.textContent = formatCountdown(targetTime - Date.now());
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-countdown]').forEach(function (el) {
            var targetTime = new Date(el.dataset.countdown.replace(' ', 'T')).getTime();
            if (isNaN(targetTime)) return;

            tick(el, targetTime);
            setInterval(function () {
                tick(el, targetTime);
            }, 1000);
        });
    });
})();
