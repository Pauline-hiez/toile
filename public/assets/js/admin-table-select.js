(function () {
    'use strict';

    function initTable(table) {
        var selectAll = table.querySelector('[data-select-all]');
        var trigger = table.querySelector('[data-bulk-trigger]');
        var formId = table.dataset.bulkForm;
        var form = formId ? document.getElementById(formId) : null;

        function rowCheckboxes() {
            return Array.from(table.querySelectorAll('.js-row-select'));
        }

        function updateState() {
            var all = rowCheckboxes();
            var checked = all.filter(function (cb) { return cb.checked; });

            if (selectAll) {
                selectAll.checked = all.length > 0 && checked.length === all.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
            }

            if (trigger) {
                trigger.hidden = checked.length === 0;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                rowCheckboxes().forEach(function (cb) {
                    cb.checked = selectAll.checked;
                });
                updateState();
            });
        }

        rowCheckboxes().forEach(function (cb) {
            cb.addEventListener('change', updateState);
        });

        if (form) {
            form.addEventListener('submit', function (e) {
                var checked = rowCheckboxes().filter(function (cb) { return cb.checked; });

                if (checked.length === 0) {
                    e.preventDefault();
                    return;
                }

                var confirmMsg = (form.dataset.confirm || 'Confirmer cette action sur %d élément(s) ?')
                    .replace('%d', checked.length);

                if (!window.confirm(confirmMsg)) {
                    e.preventDefault();
                    return;
                }

                form.querySelectorAll('input[name="ids[]"]').forEach(function (el) {
                    el.remove();
                });

                checked.forEach(function (cb) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    form.appendChild(input);
                });
            });
        }

        updateState();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bulk-table]').forEach(initTable);
    });
})();
