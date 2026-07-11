(function () {
    function getFilterForm() {
        return document.getElementById('filterForm');
    }

    function clearFieldInputs(form, fieldId) {
        const id = String(fieldId);
        Array.from(form.elements).forEach(function (element) {
            if (!element.name) {
                return;
            }

            const isFieldInput = element.name === 'fields[' + id + ']'
                || element.name.indexOf('fields[' + id + ']') === 0;

            if (!isFieldInput) {
                return;
            }

            if (element.type === 'checkbox' || element.type === 'radio') {
                element.checked = false;
            } else if (element.tagName === 'SELECT') {
                element.selectedIndex = 0;
                element.value = '';
            } else {
                element.value = '';
            }
        });
    }

    function clearAllFieldFilters(form) {
        Array.from(form.elements).forEach(function (element) {
            if (!element.name || element.name.indexOf('fields[') !== 0) {
                return;
            }

            if (element.type === 'checkbox' || element.type === 'radio') {
                element.checked = false;
            } else if (element.tagName === 'SELECT') {
                element.selectedIndex = 0;
                element.value = '';
            } else {
                element.value = '';
            }
        });
    }

    function clearDatePart(form, fieldId, part) {
        const input = form.querySelector('[name="fields[' + fieldId + '][' + part + ']"]');
        if (!input) {
            return;
        }

        if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
        }
        input.value = '';
    }

    function initSectionFilters() {
        const form = getFilterForm();
        if (!form) {
            return;
        }

        const searchInput = form.querySelector('#search');
        const initialSearch = searchInput ? String(searchInput.value || '') : '';
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                if (String(searchInput.value || '') === initialSearch) {
                    return;
                }
                const matchGroupField = form.querySelector('#match_group_field');
                if (matchGroupField) {
                    matchGroupField.remove();
                }
            });
        }

        form.addEventListener('click', function (event) {
            const clearDatePartBtn = event.target.closest('[data-clear-date-part]');
            if (clearDatePartBtn) {
                event.preventDefault();
                clearDatePart(
                    form,
                    clearDatePartBtn.getAttribute('data-field-id'),
                    clearDatePartBtn.getAttribute('data-date-part')
                );
                form.submit();
                return;
            }

            const clearOneBtn = event.target.closest('[data-clear-filter]');
            if (clearOneBtn) {
                event.preventDefault();
                clearFieldInputs(form, clearOneBtn.getAttribute('data-clear-filter'));
                form.submit();
                return;
            }

            const clearAllBtn = event.target.closest('[data-clear-all-filters]');
            if (clearAllBtn) {
                event.preventDefault();
                clearAllFieldFilters(form);
                form.submit();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSectionFilters);
    } else {
        initSectionFilters();
    }
})();
