(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var dialog = document.getElementById('reportModal');
        if (!dialog) return;

        var typeField = dialog.querySelector('[data-report-field="type"]');
        var idField = dialog.querySelector('[data-report-field="id"]');
        var redirectField = dialog.querySelector('[data-report-field="redirect"]');
        var reasonSelect = dialog.querySelector('#reportReason');
        var messageField = dialog.querySelector('#reportMessage');
        var closeBtn = dialog.querySelector('[data-report-close]');

        document.querySelectorAll('[data-report-trigger]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                typeField.value = btn.dataset.reportType || '';
                idField.value = btn.dataset.reportId || '';
                redirectField.value = btn.dataset.reportRedirect || window.location.pathname;

                if (reasonSelect) {
                    reasonSelect.value = btn.dataset.reportReason || reasonSelect.options[0].value;
                }
                if (messageField) {
                    messageField.value = '';
                }

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
    });
})();
