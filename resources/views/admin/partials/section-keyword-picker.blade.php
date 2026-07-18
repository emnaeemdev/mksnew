@php
    $selectedKeywords = collect($selectedKeywords ?? []);
    $inputName = $inputName ?? 'document_keywords';
    if ($selectedKeywords->isEmpty() && old($inputName)) {
        $old = old($inputName);
        if (is_string($old)) {
            $decoded = json_decode($old, true);
            $selectedKeywords = collect(is_array($decoded) ? $decoded : preg_split('/[,،\n]+/u', $old));
        } elseif (is_array($old)) {
            $selectedKeywords = collect($old);
        }
    }

    $selectedKeywordItems = $selectedKeywords->map(function ($item) {
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

    $fieldId = $keywordFieldId ?? ('document_keywords_' . uniqid());
@endphp

<div class="mb-0 keyword-picker document-keyword-picker"
     data-scope="document"
     data-input-name="{{ $inputName }}[]"
     data-suggest-url="{{ route('admin.keywords.suggest') }}">

    <div class="keyword-box form-control" tabindex="0">
        <div class="keyword-chips">
            @foreach($selectedKeywordItems as $kw)
                <span class="keyword-chip">
                    <span class="keyword-chip-text">{{ $kw['name'] }}</span>
                    <button type="button" class="keyword-chip-remove" aria-label="حذف">&times;</button>
                    <input type="hidden" name="{{ $inputName }}[]" value="{{ $kw['name'] }}">
                </span>
            @endforeach
        </div>
        <input type="text"
               id="{{ $fieldId }}_input"
               class="keyword-input"
               autocomplete="off"
               placeholder="ابحث عن كلمة مفتاحية مسجلة للوثائق...">
    </div>
    <div class="keyword-dropdown" hidden></div>

</div>

@once
@push('styles')
<style>
.keyword-picker{position:relative}
.keyword-box{min-height:46px;height:auto;display:flex;flex-wrap:wrap;align-items:center;gap:6px;padding:.45rem .6rem;cursor:text}
.keyword-chips{display:flex;flex-wrap:wrap;gap:6px}
.keyword-chip{display:inline-flex;align-items:center;gap:6px;background:#eef3fb;color:#2b4596;border:1px solid #c9d7f0;border-radius:999px;padding:4px 10px;font-size:.86rem;font-weight:600}
.keyword-chip-text{color:#2b4596}
.keyword-chip-remove{border:0;background:transparent;color:#6c757d;line-height:1;font-size:1rem;padding:0;cursor:pointer}
.keyword-input{border:0;outline:0;flex:1;min-width:140px;background:transparent;padding:4px 2px}
.keyword-dropdown{position:absolute;z-index:30;left:0;right:0;top:100%;margin-top:4px;background:#fff;border:1px solid #dbe3ef;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.08);max-height:240px;overflow:auto}
.keyword-dropdown-item{display:block;width:100%;border:0;background:#fff;text-align:right;padding:10px 12px;cursor:pointer}
.keyword-dropdown-item:hover{background:#f5f8fc}
.document-keyword-picker .keyword-chip-link { pointer-events: none; color: inherit; text-decoration: none; }
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.document-keyword-picker').forEach(function (root) {
        if (root.dataset.bound === '1') return;
        root.dataset.bound = '1';

        const input = root.querySelector('.keyword-input');
        const chipsWrap = root.querySelector('.keyword-chips');
        const dropdown = root.querySelector('.keyword-dropdown');
        const suggestUrl = root.dataset.suggestUrl;
        const scope = root.dataset.scope || 'document';
        const inputName = root.dataset.inputName || 'document_keywords[]';
        let timer = null;

        function selectedNames() {
            return Array.from(root.querySelectorAll('input[type="hidden"][name="' + inputName + '"]')).map(function (el) {
                return el.value.trim();
            }).filter(Boolean);
        }

        function addChip(name) {
            name = (name || '').trim();
            if (!name || selectedNames().includes(name)) return;
            const chip = document.createElement('span');
            chip.className = 'keyword-chip';
            chip.innerHTML =
                '<span class="keyword-chip-text"></span>' +
                '<button type="button" class="keyword-chip-remove" aria-label="حذف">&times;</button>' +
                '<input type="hidden" name="' + inputName + '">';
            chip.querySelector('.keyword-chip-text').textContent = name;
            chip.querySelector('input').value = name;
            chipsWrap.appendChild(chip);
            input.value = '';
            hideDropdown();
        }

        function hideDropdown() {
            dropdown.hidden = true;
            dropdown.innerHTML = '';
        }

        function showSuggestions(items) {
            const current = selectedNames();
            const filtered = items.filter(function (item) {
                return current.indexOf(item.name) === -1;
            });
            if (!filtered.length) {
                hideDropdown();
                return;
            }
            dropdown.innerHTML = filtered.map(function (item) {
                return '<button type="button" class="keyword-dropdown-item" data-name="' +
                    item.name.replace(/"/g, '&quot;') + '">' + item.name + '</button>';
            }).join('');
            dropdown.hidden = false;
        }

        function fetchSuggestions(q) {
            const url = suggestUrl + '?scope=' + encodeURIComponent(scope) + '&q=' + encodeURIComponent(q || '');
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) { showSuggestions(data.data || data.items || []); })
                .catch(function () { hideDropdown(); });
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { fetchSuggestions(input.value.trim()); }, 200);
        });

        input.addEventListener('focus', function () {
            fetchSuggestions(input.value.trim());
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (input.value.trim()) addChip(input.value.trim());
            }
        });

        dropdown.addEventListener('click', function (e) {
            const btn = e.target.closest('.keyword-dropdown-item');
            if (!btn) return;
            addChip(btn.dataset.name || btn.textContent);
        });

        chipsWrap.addEventListener('click', function (e) {
            if (e.target.closest('.keyword-chip-remove')) {
                e.target.closest('.keyword-chip').remove();
            }
        });

        document.addEventListener('click', function (e) {
            if (!root.contains(e.target)) hideDropdown();
        });
    });
});
</script>
@endpush
@endonce
