@php
    $keywordScope = $keywordScope ?? 'document';
    $selectedKeywords = collect($selectedKeywords ?? []);
    if ($selectedKeywords->isEmpty() && old('keywords')) {
        $old = old('keywords');
        if (is_string($old)) {
            $decoded = json_decode($old, true);
            $selectedKeywords = collect(is_array($decoded) ? $decoded : preg_split('/[,،\n]+/u', $old));
        } elseif (is_array($old)) {
            $selectedKeywords = collect($old);
        }
    }

    // دعم Collection من نماذج Keyword أو مصفوفة أسماء
    $selectedKeywordItems = $selectedKeywords->map(function ($item) use ($keywordScope) {
        if (is_object($item) && isset($item->name)) {
            return [
                'name' => (string) $item->name,
                'slug' => (string) ($item->slug ?: \App\Models\Keyword::makeSlug($item->name)),
            ];
        }
        $name = is_string($item) ? trim($item) : '';
        if ($name === '') {
            return null;
        }

        return [
            'name' => $name,
            'slug' => \App\Models\Keyword::makeSlug($name),
        ];
    })->filter()->values();

    $fieldId = $keywordFieldId ?? ('keywords_' . $keywordScope . '_' . uniqid());
    $locale = app()->getLocale() ?: 'ar';
    $publicBaseUrl = match ($keywordScope) {
        'post' => url($locale . '/posts/keywords'),
        'document' => url($locale . '/documents/keywords'),
        default => null,
    };
@endphp

<div class="mb-3 keyword-picker"
     data-scope="{{ $keywordScope }}"
     data-suggest-url="{{ route('admin.keywords.suggest') }}"
     data-public-base="{{ $publicBaseUrl }}">
    <label class="form-label" for="{{ $fieldId }}_input">
        <i class="fas fa-tags me-1"></i>
        الكلمات المفتاحية
    </label>
    <div class="keyword-box form-control" tabindex="0">
        <div class="keyword-chips">
            @foreach($selectedKeywordItems as $kw)
                <span class="keyword-chip">
                    @if($publicBaseUrl)
                        <a href="{{ $publicBaseUrl . '/' . rawurlencode($kw['slug']) }}"
                           class="keyword-chip-link"
                           target="_blank"
                           rel="noopener noreferrer"
                           title="عرض كل المواضيع بهذه الكلمة">{{ $kw['name'] }}</a>
                    @else
                        <span class="keyword-chip-text">{{ $kw['name'] }}</span>
                    @endif
                    <button type="button" class="keyword-chip-remove" aria-label="حذف">&times;</button>
                    <input type="hidden" name="keywords[]" value="{{ $kw['name'] }}">
                </span>
            @endforeach
        </div>
        <input type="text"
               id="{{ $fieldId }}_input"
               class="keyword-input"
               autocomplete="off"
               placeholder="اكتب أو اختر كلمة مفتاحية...">
    </div>
    <div class="keyword-dropdown" hidden></div>
    <div class="form-text">
        اضغط على الخانة لعرض الكلمات المتاحة، أو اكتب لإضافة كلمة جديدة.
        @if($publicBaseUrl)
            اضغط على اسم الكلمة لفتح نتائجها في تبويب جديد.
        @endif
    </div>
</div>

@once
<style>
.keyword-picker{position:relative}
.keyword-box{min-height:46px;height:auto;display:flex;flex-wrap:wrap;align-items:center;gap:6px;padding:.45rem .6rem;cursor:text}
.keyword-chips{display:flex;flex-wrap:wrap;gap:6px}
.keyword-chip{display:inline-flex;align-items:center;gap:6px;background:#eef3fb;color:#2b4596;border:1px solid #c9d7f0;border-radius:999px;padding:4px 10px;font-size:.86rem;font-weight:600}
.keyword-chip-link{color:#2b4596;text-decoration:underline;text-underline-offset:2px}
.keyword-chip-link:hover{color:#1a3270}
.keyword-chip-text{color:#2b4596}
.keyword-chip-remove{border:0;background:transparent;color:#6c757d;line-height:1;font-size:1rem;padding:0;cursor:pointer}
.keyword-input{border:0;outline:0;flex:1;min-width:140px;background:transparent;padding:4px 2px}
.keyword-dropdown{position:absolute;z-index:30;left:0;right:0;top:100%;margin-top:4px;background:#fff;border:1px solid #dbe3ef;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.08);max-height:240px;overflow:auto}
.keyword-option{display:flex;justify-content:space-between;gap:10px;width:100%;border:0;background:#fff;text-align:right;padding:10px 12px;cursor:pointer}
.keyword-option:hover,.keyword-option.is-active{background:#f5f8fc}
.keyword-option-count{color:#6c757d;font-size:.8rem}
.keyword-option-empty{padding:12px;color:#6c757d;font-size:.9rem}
.keyword-option-create{color:#2b4596;font-weight:700}
</style>
<script>
(function () {
    if (window.__keywordPickerReady) return;
    window.__keywordPickerReady = true;

    function makeSlug(name) {
        return String(name || '').trim().replace(/\s+/g, '-');
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function initKeywordPicker(root) {
        if (!root || root.dataset.ready === '1') return;
        root.dataset.ready = '1';

        const scope = root.dataset.scope;
        const suggestUrl = root.dataset.suggestUrl;
        const publicBase = root.dataset.publicBase || '';
        const box = root.querySelector('.keyword-box');
        const chips = root.querySelector('.keyword-chips');
        const input = root.querySelector('.keyword-input');
        const dropdown = root.querySelector('.keyword-dropdown');
        let activeIndex = -1;
        let timer = null;

        function selectedNames() {
            return Array.from(chips.querySelectorAll('input[name="keywords[]"]')).map(function (el) {
                return el.value;
            });
        }

        function hasName(name) {
            return selectedNames().some(function (n) { return n === name; });
        }

        function chipHtml(name, slug) {
            slug = slug || makeSlug(name);
            const label = escapeHtml(name);
            let labelHtml = '<span class="keyword-chip-text">' + label + '</span>';
            if (publicBase) {
                const href = publicBase.replace(/\/$/, '') + '/' + encodeURIComponent(slug);
                labelHtml = '<a href="' + href + '" class="keyword-chip-link" target="_blank" rel="noopener noreferrer" title="عرض كل المواضيع بهذه الكلمة">' + label + '</a>';
            }
            return labelHtml +
                '<button type="button" class="keyword-chip-remove" aria-label="حذف">&times;</button>' +
                '<input type="hidden" name="keywords[]" value="">';
        }

        function addChip(name, slug) {
            name = (name || '').trim().replace(/\s+/g, ' ');
            if (!name || hasName(name)) return;
            const chip = document.createElement('span');
            chip.className = 'keyword-chip';
            chip.innerHTML = chipHtml(name, slug);
            chip.querySelector('input').value = name;
            chips.appendChild(chip);
            input.value = '';
            hideDropdown();
        }

        function hideDropdown() {
            dropdown.hidden = true;
            dropdown.innerHTML = '';
            activeIndex = -1;
        }

        function renderDropdown(list, query) {
            const selected = selectedNames();
            const filtered = list.filter(function (item) {
                return selected.indexOf(item.name) === -1;
            });
            dropdown.innerHTML = '';

            if (filtered.length === 0 && !query) {
                dropdown.innerHTML = '<div class="keyword-option-empty">لا توجد كلمات مفتاحية بعد</div>';
                dropdown.hidden = false;
                return;
            }

            filtered.forEach(function (item, idx) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'keyword-option';
                btn.dataset.index = String(idx);
                btn.innerHTML = '<span>' + escapeHtml(item.name) + '</span><span class="keyword-option-count">' + (item.usage_count || 0) + '</span>';
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    addChip(item.name, item.slug);
                });
                dropdown.appendChild(btn);
            });

            if (query && !hasName(query) && !filtered.some(function (i) { return i.name === query; })) {
                const createBtn = document.createElement('button');
                createBtn.type = 'button';
                createBtn.className = 'keyword-option keyword-option-create';
                createBtn.textContent = 'إضافة «' + query + '»';
                createBtn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    addChip(query);
                });
                dropdown.appendChild(createBtn);
            }

            dropdown.hidden = false;
        }

        function fetchSuggestions(query) {
            const url = suggestUrl + '?scope=' + encodeURIComponent(scope) +
                '&q=' + encodeURIComponent(query || '') +
                '&limit=12';
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    renderDropdown((json && json.data) ? json.data : [], query);
                })
                .catch(function () {
                    renderDropdown([], query);
                });
        }

        box.addEventListener('click', function (e) {
            if (e.target.closest('.keyword-chip-link') || e.target.closest('.keyword-chip-remove')) {
                return;
            }
            input.focus();
            fetchSuggestions(input.value.trim());
        });
        input.addEventListener('focus', function () {
            fetchSuggestions(input.value.trim());
        });
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                fetchSuggestions(input.value.trim());
            }, 180);
        });
        input.addEventListener('keydown', function (e) {
            const options = dropdown.querySelectorAll('.keyword-option');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, options.length - 1);
                options.forEach(function (el, i) { el.classList.toggle('is-active', i === activeIndex); });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                options.forEach(function (el, i) { el.classList.toggle('is-active', i === activeIndex); });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && options[activeIndex]) {
                    options[activeIndex].dispatchEvent(new Event('mousedown'));
                } else if (input.value.trim()) {
                    addChip(input.value.trim());
                }
            } else if (e.key === 'Backspace' && !input.value) {
                const all = chips.querySelectorAll('.keyword-chip');
                if (all.length) all[all.length - 1].remove();
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });
        chips.addEventListener('click', function (e) {
            const btn = e.target.closest('.keyword-chip-remove');
            if (!btn) return;
            e.preventDefault();
            btn.closest('.keyword-chip').remove();
        });
        document.addEventListener('click', function (e) {
            if (!root.contains(e.target)) hideDropdown();
        });
    }

    function boot() {
        document.querySelectorAll('.keyword-picker').forEach(initKeywordPicker);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
@endonce
