@php
    $fieldId = $fieldId ?? 'date';
    $inputName = $inputName ?? "custom_fields[{$fieldId}]";
    $required = !empty($required);
    $value = trim((string) ($value ?? ''));
    $displayValue = $value;
    if ($value !== '' && preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $m)) {
        $displayValue = $m[3] . '-' . $m[2] . '-' . $m[1];
    }
@endphp

<div class="admin-date-smart" data-date-field="{{ $fieldId }}">
    <div class="input-group">
        <input type="text"
               class="form-control date-smart-display"
               id="date_display_{{ $fieldId }}"
               value="{{ $displayValue }}"
               placeholder="مثال: 30-10-1990 أو 1990/10/30"
               autocomplete="off"
               {{ $required ? 'required' : '' }}>
        <button type="button" class="btn btn-outline-secondary date-smart-picker-btn" title="اختيار من التقويم">
            <i class="fas fa-calendar-alt"></i>
        </button>
        <input type="date" class="date-smart-native" tabindex="-1" aria-hidden="true">
    </div>
    <input type="hidden"
           class="date-part-value date-smart-value"
           id="custom_field_{{ $fieldId }}"
           name="{{ $inputName }}"
           value="{{ $value }}">
    <div class="form-text">
        اكتب التاريخ بأي صيغة شائعة (يوم-شهر-سنة أو سنة-شهر-يوم) أو اختره من أيقونة التقويم.
        <span class="date-smart-hint text-success" hidden></span>
    </div>
</div>
