(function () {
    'use strict';

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function formatCountdown(diffMs) {
        if (diffMs <= 0) {
            return '00J : 00H : 00MIN';
        }
        var totalMinutes = Math.floor(diffMs / 60000);
        var days = Math.floor(totalMinutes / (60 * 24));
        var hours = Math.floor((totalMinutes % (60 * 24)) / 60);
        var minutes = totalMinutes % 60;
        return pad(days) + 'J : ' + pad(hours) + 'H : ' + pad(minutes) + 'MIN';
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
            }, 30000);
        });
    });
})();
