@extends('admin.layouts.app')

@section('title', 'إضافة حقل مخصص جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إضافة حقل مخصص جديد</h3>
                    <div>
                        @if(request('section'))
                            @php
                                $section = \App\Models\DocumentSection::find(request('section'));
                            @endphp
                            @if($section)
                                <span class="badge bg-info me-2">{{ $section->name }}</span>
                                <a href="{{ route('admin.document-sections.show', $section) }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> العودة للقسم
                                </a>
                            @endif
                        @else
                            <a href="{{ route('admin.document-custom-fields.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> العودة للقائمة
                            </a>
                        @endif
                    </div>
                </div>
                
                <form action="{{ route('admin.document-custom-fields.store') }}" method="POST" id="fieldForm">
                    @csrf
                    @if(request('section'))
                        <input type="hidden" name="section_id" value="{{ request('section') }}">
                    @endif
                    
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-8">
                                <!-- معلومات الحقل الأساسية -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">معلومات الحقل الأساسية</h6>
                                    </div>
                                    <div class="card-body">
                                        @if(!request('section'))
                                        <div class="mb-3">
                                            <label for="section_id" class="form-label">القسم <span class="text-danger">*</span></label>
                                            <select class="form-select @error('section_id') is-invalid @enderror" 
                                                    id="section_id" name="section_id" required>
                                                <option value="">اختر القسم</option>
                                                @foreach($sections as $sec)
                                                    <option value="{{ $sec->id }}" {{ old('section_id') == $sec->id ? 'selected' : '' }}>
                                                        {{ $sec->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        @endif

                                        <div class="mb-3">
                                            <label for="label" class="form-label">تسمية الحقل <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('label') is-invalid @enderror" 
                                                   id="label" name="label" value="{{ old('label') }}" 
                                                   placeholder="مثال: رقم القرار، تاريخ الإصدار" required>
                                            @error('label')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">هذا النص سيظهر للمستخدم كتسمية للحقل</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="name" class="form-label">اسم الحقل التقني <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" 
                                                   placeholder="decision_number" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">يُستخدم في قاعدة البيانات (أحرف إنجليزية وأرقام و _ فقط)</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="type" class="form-label">نوع الحقل <span class="text-danger">*</span></label>
                                            <select class="form-select @error('type') is-invalid @enderror" 
                                                    id="type" name="type" required>
                                                <option value="">اختر نوع الحقل</option>
                                                <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>نص قصير</option>
                                                <option value="textarea" {{ old('type') == 'textarea' ? 'selected' : '' }}>نص طويل</option>
                                                <option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>رقم</option>
                                                <option value="date" {{ old('type') == 'date' ? 'selected' : '' }}>تاريخ</option>
                                                <option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>قائمة منسدلة</option>
                                                <option value="multiselect" {{ old('type') == 'multiselect' ? 'selected' : '' }}>اختيارات متعددة</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="placeholder" class="form-label">النص التوضيحي</label>
                                            <input type="text" class="form-control @error('placeholder') is-invalid @enderror" 
                                                   id="placeholder" name="placeholder" value="{{ old('placeholder') }}" 
                                                   placeholder="مثال: أدخل رقم القرار">
                                            @error('placeholder')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">نص يظهر داخل الحقل لتوضيح المطلوب</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="help_text" class="form-label">نص المساعدة</label>
                                            <textarea class="form-control @error('help_text') is-invalid @enderror" 
                                                      id="help_text" name="help_text" rows="2" 
                                                      placeholder="شرح إضافي لمساعدة المستخدم">{{ old('help_text') }}</textarea>
                                            @error('help_text')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">نص يظهر أسفل الحقل لتوضيح كيفية ملئه</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- خيارات القائمة المنسدلة -->
                                <div class="card mb-4" id="optionsCard" style="display: none;">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">خيارات القائمة</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">الخيارات المتاحة</label>
                                            <div id="optionsContainer">
                                                <div class="option-item mb-2">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="options[]" placeholder="الخيار الأول">
                                                        <button type="button" class="btn btn-outline-danger remove-option" disabled>
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="addOption">
                                                <i class="fas fa-plus"></i> إضافة خيار
                                            </button>
                                            <div class="form-text">أضف الخيارات المتاحة للاختيار من بينها</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- قواعد التحقق -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">قواعد التحقق</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="validation_rules" class="form-label">قواعد التحقق المخصصة</label>
                                            <input type="text" class="form-control @error('validation_rules') is-invalid @enderror" 
                                                   id="validation_rules" name="validation_rules" value="{{ old('validation_rules') }}" 
                                                   placeholder="مثال: min:3|max:100">
                                            @error('validation_rules')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                قواعد Laravel للتحقق (اختياري). مثال: min:3|max:100|regex:/^[0-9]+$/
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- إعدادات الحقل -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">إعدادات الحقل</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">ترتيب العرض</label>
                                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}" 
                                                   min="0" placeholder="1">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">الحقول ذات الأرقام الأقل تظهر أولاً</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="is_required" name="is_required" value="1" 
                                                       {{ old('is_required') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_required">
                                                    حقل مطلوب
                                                </label>
                                            </div>
                                            <div class="form-text">إجبار المستخدم على ملء هذا الحقل</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="is_active" name="is_active" value="1" 
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    تفعيل الحقل
                                                </label>
                                            </div>
                                            <div class="form-text">الحقول غير المفعلة لن تظهر في النماذج</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- معاينة الحقل -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">معاينة الحقل</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="fieldPreview">
                                            <p class="text-muted">اختر نوع الحقل لرؤية المعاينة</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>ملاحظات:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>اختر اسماً تقنياً واضحاً ومميزاً</li>
                                        <li>يمكن تعديل جميع الإعدادات لاحقاً</li>
                                        <li>الحقول المطلوبة يجب ملؤها عند إضافة الوثائق</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                @if(request('section'))
                                    <a href="{{ route('admin.document-sections.show', request('section')) }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> إلغاء
                                    </a>
                                @else
                                    <a href="{{ route('admin.document-custom-fields.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> إلغاء
                                    </a>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ الحقل
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // تحديث الاسم التقني تلقائياً من التسمية
    $('#label').on('input', function() {
        const label = $(this).val();
        const name = label.toLowerCase()
            .replace(/[^\u0600-\u06FF\w\s]/g, '') // إزالة الرموز
            .replace(/[\u0600-\u06FF]/g, '') // إزالة الأحرف العربية
            .replace(/\s+/g, '_') // استبدال المسافات بـ _
            .replace(/_+/g, '_') // إزالة _ المتكررة
            .trim('_');
        
        if (name && !$('#name').data('manual-edit')) {
            $('#name').val(name);
        }
        
        updatePreview();
    });
    
    // منع التحديث التلقائي عند التعديل اليدوي للاسم التقني
    $('#name').on('input', function() {
        $(this).data('manual-edit', true);
        updatePreview();
    });
    
    // تحديث المعاينة عند تغيير نوع الحقل
    $('#type').on('change', function() {
        const type = $(this).val();
        
        // إظهار/إخفاء خيارات القائمة المنسدلة
        if (type === 'select' || type === 'multiselect') {
            $('#optionsCard').show();
        } else {
            $('#optionsCard').hide();
        }
        
        updatePreview();
    });
    
    // إظهار قسم الخيارات عند تحميل الصفحة إذا كان النوع محدد مسبقاً
    const initialType = $('#type').val();
    if (initialType === 'select' || initialType === 'multiselect') {
        $('#optionsCard').show();
    }
    
    // تحديث المعاينة عند تغيير أي حقل
    $('#placeholder, #help_text, #is_required').on('input change', updatePreview);
    
    // إضافة خيار جديد
    $('#addOption').on('click', function() {
        const optionHtml = `
            <div class="option-item mb-2">
                <div class="input-group">
                    <input type="text" class="form-control" name="options[]" placeholder="خيار جديد">
                    <button type="button" class="btn btn-outline-danger remove-option">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        $('#optionsContainer').append(optionHtml);
        updateRemoveButtons();
    });
    
    // حذف خيار
    $(document).on('click', '.remove-option', function() {
        $(this).closest('.option-item').remove();
        updateRemoveButtons();
    });
    
    // تحديث أزرار الحذف
    function updateRemoveButtons() {
        const options = $('.option-item');
        if (options.length <= 1) {
            $('.remove-option').prop('disabled', true);
        } else {
            $('.remove-option').prop('disabled', false);
        }
    }
    
    // تحديث معاينة الحقل
    function updatePreview() {
        const type = $('#type').val();
        const label = $('#label').val() || 'تسمية الحقل';
        const placeholder = $('#placeholder').val();
        const helpText = $('#help_text').val();
        const isRequired = $('#is_required').is(':checked');
        
        let previewHtml = '';
        
        if (!type) {
            previewHtml = '<p class="text-muted">اختر نوع الحقل لرؤية المعاينة</p>';
        } else {
            previewHtml = `<label class="form-label">${label}`;
            if (isRequired) {
                previewHtml += ' <span class="text-danger">*</span>';
            }
            previewHtml += '</label>';
            
            switch (type) {
                case 'text':
                    previewHtml += `<input type="text" class="form-control" placeholder="${placeholder}" disabled>`;
                    break;
                case 'textarea':
                    previewHtml += `<textarea class="form-control" rows="3" placeholder="${placeholder}" disabled></textarea>`;
                    break;
                case 'number':
                    previewHtml += `<input type="number" class="form-control" placeholder="${placeholder}" disabled>`;
                    break;
                case 'date':
                    previewHtml += `<input type="date" class="form-control" disabled>`;
                    break;
                case 'select':
                    previewHtml += `<select class="form-select" disabled><option>اختر...</option></select>`;
                    break;
                case 'multiselect':
                    previewHtml += `<select class="form-select" multiple disabled><option>خيار 1</option><option>خيار 2</option></select>`;
                    break;
            }
            
            if (helpText) {
                previewHtml += `<div class="form-text">${helpText}</div>`;
            }
        }
        
        $('#fieldPreview').html(previewHtml);
    }
    
    // التحقق من صحة النموذج قبل الإرسال
    $('#fieldForm').on('submit', function(e) {
        const type = $('#type').val();
        
        // إزالة الحقول الفارغة من الخيارات
        $('input[name="options[]"]').each(function() {
            if ($(this).val().trim() === '') {
                $(this).closest('.option-item').remove();
            }
        });
        
        // التحقق من وجود خيارات للقوائم المنسدلة
        if ((type === 'select' || type === 'multiselect')) {
            const options = $('input[name="options[]"]').filter(function() {
                return $(this).val().trim() !== '';
            });
            
            if (options.length < 2) {
                e.preventDefault();
                alert('يجب إضافة خيارين على الأقل للقائمة المنسدلة');
                return false;
            }
        }
        
        // إظهار مؤشر التحميل
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');
    });
    
    // تهيئة المعاينة
    updatePreview();
    updateRemoveButtons();
});
</script>
@endpush

@push('styles')
<style>
.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

.option-item {
    transition: all 0.3s ease;
}

.option-item:hover {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 0.25rem;
    margin: -0.25rem;
}

#fieldPreview {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
    border: 1px dashed #dee2e6;
}

#fieldPreview .form-control,
#fieldPreview .form-select {
    margin-bottom: 0.5rem;
}

.alert-info {
    border-left: 4px solid #17a2b8;
}
</style>
@endpush