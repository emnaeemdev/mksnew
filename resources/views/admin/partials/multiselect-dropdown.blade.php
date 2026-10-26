@php
    $selectedValues = array_values($selectedValues ?? []);
    $placeholder = $placeholder ?? 'اختر...';
    $dropdownId = 'custom_field_' . $fieldId;
@endphp
<div class="dropdown admin-multiselect w-100"
     data-multiselect
     data-placeholder="{{ $placeholder }}">
    <button class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center text-start"
            type="button"
            id="{{ $dropdownId }}"
            data-multiselect-toggle
            aria-expanded="false">
        <span class="admin-multiselect-label text-truncate">
            {{ count($selectedValues) ? implode('، ', $selectedValues) : $placeholder }}
        </span>
        <i class="fas fa-chevron-down ms-2"></i>
    </button>
    <ul class="dropdown-menu w-100 p-2 admin-multiselect-menu"
        aria-labelledby="{{ $dropdownId }}"
        style="max-height: 260px; overflow-y: auto;">
        @forelse($options as $option)
            <li>
                <label class="dropdown-item rounded d-flex align-items-center gap-2 mb-0">
                    <input type="checkbox"
                           class="form-check-input m-0 admin-multiselect-checkbox"
                           name="{{ $name }}[]"
                           value="{{ $option }}"
                           {{ in_array($option, $selectedValues) ? 'checked' : '' }}>
                    <span>{{ $option }}</span>
                </label>
            </li>
        @empty
            <li class="text-muted small px-2">لا توجد خيارات</li>
        @endforelse
    </ul>
</div>
