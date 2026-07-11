(function (window, document) {
    'use strict';

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function toWesternDigits(str) {
        var map = {
            '٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4',
            '٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9',
            '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4',
            '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9'
        };
        return String(str).replace(/[٠-٩۰-۹]/g, function (ch) {
            return map[ch] || ch;
        });
    }

    function isValidYmd(y, m, d) {
        y = parseInt(y, 10);
        m = parseInt(m, 10);
        d = parseInt(d, 10);
        if (!y || !m || !d || m < 1 || m > 12 || d < 1 || d > 31) return false;
        var dt = new Date(y, m - 1, d);
        return dt.getFullYear() === y && (dt.getMonth() + 1) === m && dt.getDate() === d;
    }

    function expandYear(y) {
        y = parseInt(y, 10);
        if (y >= 100) return y;
        // سنتان: 00-49 → 2000s ، 50-99 → 1900s (مناسب للوثائق القانونية)
        return y <= 49 ? (2000 + y) : (1900 + y);
    }

    function parseFlexibleDate(raw) {
        raw = toWesternDigits(String(raw || '').trim());
        if (!raw) return null;

        raw = raw.replace(/[.\u060c،]/g, '/').replace(/\s+/g, '');

        var m;

        // YYYY-MM-DD or YYYY/MM/DD
        m = raw.match(/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/);
        if (m && isValidYmd(m[1], m[2], m[3])) {
            return m[1] + '-' + pad(m[2]) + '-' + pad(m[3]);
        }

        // DD-MM-YYYY or DD/MM/YYYY
        m = raw.match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/);
        if (m && isValidYmd(m[3], m[2], m[1])) {
            return m[3] + '-' + pad(m[2]) + '-' + pad(m[1]);
        }

        // DD-MM-YY
        m = raw.match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{2})$/);
        if (m) {
            var y2 = expandYear(m[3]);
            if (isValidYmd(y2, m[2], m[1])) {
                return y2 + '-' + pad(m[2]) + '-' + pad(m[1]);
            }
        }

        // YYYYMMDD
        m = raw.match(/^(\d{4})(\d{2})(\d{2})$/);
        if (m && isValidYmd(m[1], m[2], m[3])) {
            return m[1] + '-' + m[2] + '-' + m[3];
        }

        // DDMMYYYY
        m = raw.match(/^(\d{2})(\d{2})(\d{4})$/);
        if (m && isValidYmd(m[3], m[2], m[1])) {
            return m[3] + '-' + m[2] + '-' + m[1];
        }

        return null;
    }

    function formatDisplay(iso) {
        if (!iso) return '';
        var m = String(iso).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return iso;
        return m[3] + '-' + m[2] + '-' + m[1];
    }

    function applyValue(root, iso, options) {
        options = options || {};
        var display = root.querySelector('.date-smart-display');
        var hidden = root.querySelector('.date-smart-value');
        var native = root.querySelector('.date-smart-native');
        var hint = root.querySelector('.date-smart-hint');

        if (hidden) hidden.value = iso || '';
        if (native) native.value = iso || '';

        if (display && options.updateDisplay !== false) {
            display.value = iso ? formatDisplay(iso) : (options.keepRaw || display.value);
            display.classList.toggle('is-invalid', !!(options.invalid));
            display.classList.toggle('is-valid', !!(iso && !options.invalid));
        }

        if (hint) {
            if (iso) {
                hint.hidden = false;
                hint.textContent = 'تم التعرف: ' + formatDisplay(iso);
            } else if (options.invalid) {
                hint.hidden = false;
                hint.classList.remove('text-success');
                hint.classList.add('text-danger');
                hint.textContent = 'صيغة التاريخ غير واضحة';
            } else {
                hint.hidden = true;
                hint.textContent = '';
                hint.classList.add('text-success');
                hint.classList.remove('text-danger');
            }
            if (iso) {
                hint.classList.add('text-success');
                hint.classList.remove('text-danger');
            }
        }
    }

    function syncFromDisplay(root) {
        var display = root.querySelector('.date-smart-display');
        if (!display) return;
        var raw = display.value.trim();
        if (!raw) {
            applyValue(root, '', { updateDisplay: true });
            return;
        }
        var iso = parseFlexibleDate(raw);
        if (iso) {
            applyValue(root, iso, { updateDisplay: true });
        } else {
            applyValue(root, '', { updateDisplay: false, keepRaw: raw, invalid: true });
        }
    }

    function bindSmartDate(root) {
        if (!root || root.dataset.bound === '1') return;
        root.dataset.bound = '1';

        var display = root.querySelector('.date-smart-display');
        var native = root.querySelector('.date-smart-native');
        var pickerBtn = root.querySelector('.date-smart-picker-btn');
        var hidden = root.querySelector('.date-smart-value');

        if (native) {
            native.style.position = 'absolute';
            native.style.opacity = '0';
            native.style.width = '1px';
            native.style.height = '1px';
            native.style.pointerEvents = 'none';
            if (hidden && hidden.value) native.value = hidden.value;
        }

        if (display) {
            display.addEventListener('blur', function () {
                syncFromDisplay(root);
            });
            display.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    syncFromDisplay(root);
                }
            });
        }

        if (pickerBtn && native) {
            pickerBtn.addEventListener('click', function () {
                if (typeof native.showPicker === 'function') {
                    try { native.showPicker(); return; } catch (err) {}
                }
                native.style.pointerEvents = 'auto';
                native.style.opacity = '1';
                native.style.width = 'auto';
                native.style.height = 'auto';
                native.focus();
                native.click();
            });
            native.addEventListener('change', function () {
                if (native.value) {
                    applyValue(root, native.value, { updateDisplay: true });
                }
                native.style.pointerEvents = 'none';
                native.style.opacity = '0';
                native.style.width = '1px';
                native.style.height = '1px';
            });
        }
    }

    function initAll(scope) {
        (scope || document).querySelectorAll('.admin-date-smart, .admin-date-selects').forEach(function (root) {
            if (root.classList.contains('admin-date-smart') || root.querySelector('.date-smart-display')) {
                bindSmartDate(root);
            }
        });
    }

    function buildDateSelectHtml(fieldId, options) {
        options = options || {};
        var required = options.required ? 'required' : '';
        var name = options.name || ('custom_fields[' + fieldId + ']');
        var value = options.value || '';
        var display = value ? formatDisplay(value) : '';

        return '' +
            '<div class="admin-date-smart" data-date-field="' + fieldId + '">' +
            '<div class="input-group">' +
            '<input type="text" class="form-control date-smart-display" id="date_display_' + fieldId + '" value="' + display + '" placeholder="مثال: 30-10-1990 أو 1990/10/30" autocomplete="off" ' + required + '>' +
            '<button type="button" class="btn btn-outline-secondary date-smart-picker-btn" title="اختيار من التقويم"><i class="fas fa-calendar-alt"></i></button>' +
            '<input type="date" class="date-smart-native" tabindex="-1" aria-hidden="true">' +
            '</div>' +
            '<input type="hidden" class="date-part-value date-smart-value" id="custom_field_' + fieldId + '" name="' + name + '" value="' + value + '">' +
            '<div class="form-text">اكتب التاريخ بأي صيغة شائعة أو اختره من أيقونة التقويم. <span class="date-smart-hint text-success" hidden></span></div>' +
            '</div>';
    }

    window.AdminDateSelects = {
        initAll: initAll,
        bind: bindSmartDate,
        buildHtml: buildDateSelectHtml,
        parse: parseFlexibleDate
    };

    document.addEventListener('DOMContentLoaded', function () {
        initAll(document);
    });

    // قبل الإرسال: تأكد من تحويل النص إلى قيمة مخفية
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        form.querySelectorAll('.admin-date-smart').forEach(function (root) {
            syncFromDisplay(root);
        });
    }, true);
})(window, document);
