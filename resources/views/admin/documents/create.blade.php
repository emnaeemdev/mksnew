@extends('admin.layouts.app')

@section('title', 'إضافة وثيقة جديدة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0">إضافة وثيقة جديدة</h3>
                        @if($selectedSection)
                            <div class="text-muted small mt-1">القسم: <strong>{{ $selectedSection->name }}</strong></div>
                        @endif
                    </div>
                    <a href="{{ $selectedSection ? route('admin.documents.index', ['section_id' => $selectedSection->id]) : route('admin.documents.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> العودة للقائمة
                    </a>
                </div>
                
                <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data" id="documentForm">
                    @csrf
                    
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <h6>يرجى تصحيح الأخطاء التالية:</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="row">
                            <!-- العنوان والمحتوى -->
                            <div class="col-lg-8">
                                <!-- العنوان -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">العنوان</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">
                                                العنوان <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                                   id="title" name="title" value="{{ old('title') }}" 
                                                   placeholder="أدخل عنوان الوثيقة" required>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">سيتم استخدام هذا العنوان في نتائج البحث وإنشاء الرابط</div>
                                        </div>
                                        @include('admin.partials.keyword-picker', [
                                            'keywordScope' => 'document',
                                            'selectedKeywords' => old('keywords', []),
                                        ])
                                    </div>
                                </div>
                                
                                <!-- الحقول المخصصة -->
                                @if($customFields->count() > 0)
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            @foreach($customFields as $field)
                                                <div class="mb-3">
                                                    <label for="custom_field_{{ $field->id }}" class="form-label">
                                                        {{ $field->label }}
                                                        @if($field->is_required)
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </label>
                                                    
                                                    @php
                                                        $currentValue = old('custom_fields.' . $field->id, '');
                                                    @endphp
                                                    
                                                    @switch($field->type)
                                                        @case('text')
                                                            <input type="text" class="form-control" 
                                                                   id="custom_field_{{ $field->id }}" 
                                                                   name="custom_fields[{{ $field->id }}]" 
                                                                   value="{{ $currentValue }}" 
                                                                   placeholder="{{ $field->placeholder }}" 
                                                                   {{ $field->is_required ? 'required' : '' }}>
                                                            @break
                                                            
                                                        @case('textarea')
                                                            <textarea class="form-control" 
                                                                      id="custom_field_{{ $field->id }}" 
                                                                      name="custom_fields[{{ $field->id }}]" 
                                                                      rows="4" 
                                                                      placeholder="{{ $field->placeholder }}" 
                                                                      {{ $field->is_required ? 'required' : '' }}>{{ $currentValue }}</textarea>
                                                            @break
                                                            
                                                        @case('number')
                                                            <input type="number" class="form-control" 
                                                                   id="custom_field_{{ $field->id }}" 
                                                                   name="custom_fields[{{ $field->id }}]" 
                                                                   value="{{ $currentValue }}" 
                                                                   placeholder="{{ $field->placeholder }}" 
                                                                   {{ $field->is_required ? 'required' : '' }}>
                                                            @break
                                                            
                                                        @case('date')
                                                            @include('admin.partials.date-selects', [
                                                                'fieldId' => $field->id,
                                                                'value' => $currentValue,
                                                                'required' => (bool) $field->is_required,
                                                            ])
                                                            @break
                                                            
                                                        @case('select')
                                                            <select class="form-select" 
                                                                    id="custom_field_{{ $field->id }}" 
                                                                    name="custom_fields[{{ $field->id }}]" 
                                                                    {{ $field->is_required ? 'required' : '' }}>
                                                                <option value="">اختر...</option>
                                                                @if($field->options)
                                                                    @foreach($field->options as $option)
                                                                        <option value="{{ $option }}" 
                                                                                {{ $currentValue == $option ? 'selected' : '' }}>
                                                                            {{ $option }}
                                                                        </option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            @break
                                                            
                                                        @case('multiselect')
                                                            @php
                                                                $selectedValues = is_array($currentValue) ? $currentValue : json_decode($currentValue, true) ?? [];
                                                            @endphp
                                                            <select class="form-select" 
                                                                    id="custom_field_{{ $field->id }}" 
                                                                    name="custom_fields[{{ $field->id }}][]" 
                                                                    multiple 
                                                                    {{ $field->is_required ? 'required' : '' }}>
                                                                @if($field->options)
                                                                    @foreach($field->options as $option)
                                                                        <option value="{{ $option }}" 
                                                                                {{ in_array($option, $selectedValues) ? 'selected' : '' }}>
                                                                            {{ $option }}
                                                                        </option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            @break

                                                        @case('file')
                                                            <input type="file" class="form-control"
                                                                   id="custom_field_{{ $field->id }}"
                                                                   name="custom_fields[{{ $field->id }}][]"
                                                                   multiple
                                                                   {{ $field->is_required ? 'required' : '' }}>
                                                            <div class="form-text">يمكنك اختيار عدة ملفات</div>
                                                            @break
                                                    @endswitch
                                                    
                                                    @if($field->help_text)
                                                        <div class="form-text">{{ $field->help_text }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                @if($selectedSection)
                                                    <strong>لا توجد حقول مخصصة في هذا القسم</strong><br>
                                                    يرجى إضافة حقول مخصصة للقسم المحدد من إعدادات الأقسام أو اختيار قسم آخر.
                                                @else
                                                    <strong>يرجى اختيار قسم أولاً</strong><br>
                                                    اختر قسماً من القائمة المنسدلة لعرض الحقول المخصصة المتاحة.
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- المحتوى -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">المحتوى</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="content" class="form-label">
                                                المحتوى <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control tinymce-editor @error('content') is-invalid @enderror" 
                                                      id="content" name="content" rows="15" 
                                                      placeholder="أدخل محتوى الوثيقة" required>{{ old('content') }}</textarea>
                                            @error('content')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">سيتم عرض هذا المحتوى في صفحة الوثيقة ونتائج البحث</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- الإعدادات الجانبية -->
                            <div class="col-lg-4">
                                <!-- إعدادات النشر -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">إعدادات النشر</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- القسم -->
                                        <div class="mb-3">
                                            @if($selectedSection)
                                                <!-- عرض القسم المحدد مسبقاً -->
                                                <label class="form-label">القسم</label>
                                                <div class="form-control-plaintext bg-light p-2 rounded border">
                                                    <i class="fas fa-folder text-primary me-2"></i>
                                                    {{ $selectedSection->name }}
                                                </div>
                                                <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                                                <div class="form-text">
                                                    <i class="fas fa-info-circle text-info"></i>
                                                    تم تحديد القسم تلقائياً. 
                                                    <a href="{{ route('admin.documents.create') }}" class="text-decoration-none">
                                                        انقر هنا لاختيار قسم آخر
                                                    </a>
                                                </div>
                                            @else
                                                <!-- اختيار القسم -->
                                                <label for="section_id" class="form-label">القسم <span class="text-danger">*</span></label>
                                                <select class="form-select @error('section_id') is-invalid @enderror" 
                                                        id="section_id" name="section_id" required>
                                                    <option value="">اختر القسم</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}" 
                                                                {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                            {{ $section->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('section_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>
                                        
                                        <!-- حالة النشر -->
                                        <div class="mb-3">
                                            <label for="is_published" class="form-label">حالة النشر</label>
                                            <select class="form-select @error('is_published') is-invalid @enderror" 
                                                    id="is_published" name="is_published">
                                                <option value="0" {{ old('is_published', '1') == '0' ? 'selected' : '' }}>مسودة</option>
                                                <option value="1" {{ old('is_published', '1') == '1' ? 'selected' : '' }}>منشور</option>
                                            </select>
                                            @error('is_published')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- تاريخ النشر -->
                                        <div class="mb-3" id="publishedAtContainer" style="display: none;">
                                            <label for="published_at" class="form-label">تاريخ النشر</label>
                                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" 
                                                   id="published_at" name="published_at" value="{{ old('published_at') }}">
                                            @error('published_at')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">اتركه فارغاً لاستخدام التاريخ الحالي</div>
                                        </div>
                                        
                                        <!-- وثيقة مميزة -->
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input @error('is_featured') is-invalid @enderror" 
                                                       type="checkbox" id="is_featured" name="is_featured" value="1" 
                                                       {{ old('is_featured') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_featured">
                                                    وثيقة مميزة
                                                </label>
                                                @error('is_featured')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-text">الوثائق المميزة تظهر في المقدمة</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">ترتيب العرض</label>
                                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">الأرقام الأصغر تظهر أولاً</div>
                                        </div>
                                    </div>
                                </div>
                                

                                
                                <!-- معلومات إضافية -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">معلومات إضافية</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">الكاتب</label>
                                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                            <div class="form-text">سيتم حفظ الوثيقة باسمك</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">تاريخ الإنشاء</label>
                                            <input type="text" class="form-control" value="{{ now()->format('Y-m-d H:i') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- رفع الملفات -->
                                <div class="card mt-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">الملفات المرفقة</h5>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addDocumentFileRow()">
                                            <i class="fas fa-plus"></i> إضافة ملف
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="document-files-container">
                                            <div class="document-file-row border rounded p-3 mb-3">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-md-5">
                                                        <label class="form-label">الملف</label>
                                                        <input type="file" class="form-control document-file-input" name="document_files[]" onchange="updateDocumentFileDisplayName(this)">
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label">اسم العرض للزوار</label>
                                                        <input type="text" class="form-control" name="file_display_names[]" placeholder="اسم الملف كما سيظهر للزوار">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-outline-danger w-100" onclick="removeDocumentFileRow(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-text">يمكنك إضافة عدة ملفات — اضغط «إضافة ملف» لكل ملف جديد</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="admin-form-actions">
                            <div class="admin-form-actions__secondary">
                                <a href="{{ route('admin.documents.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                            </div>
                            
                            <div class="admin-form-actions__primary">
                                <button type="submit" name="action" value="publish" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> حفظ ونشر
                                </button>
                                <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                                    <i class="fas fa-save"></i> حفظ كمسودة
                                </button>
                            </div>
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
    // التحقق من القسم المحدد مسبقاً عند تحميل الصفحة
    const initialSectionId = $('#section_id').val();
    if (initialSectionId) {
        $('#section_id').trigger('change');
    }
    
    // إظهار/إخفاء تاريخ النشر
    $('#is_published').on('change', function() {
        if ($(this).val() == '1') {
            $('#publishedAtContainer').show();
        } else {
            $('#publishedAtContainer').hide();
        }
    });
    
    // تحديث الحقول المخصصة عند تغيير القسم
    $('#section_id').on('change', function() {
        const sectionId = $(this).val();
        const titleCard = $('.col-lg-8 .card').first(); // بطاقة العنوان
        const contentCard = $('.col-lg-8 .card').eq(1); // بطاقة المحتوى
        const customFieldsContainer = $('#customFieldsContainer');
        const originalWarningCard = $('.col-lg-8 .card').eq(1); // البطاقة التي تحتوي على رسالة التحذير الأصلية
        
        if (sectionId) {
            // إظهار حقول العنوان والمحتوى مع الحقول المخصصة
            titleCard.show();
            contentCard.show();
            
            // إخفاء رسالة التحذير الأصلية إذا كانت موجودة
            $('.col-lg-8 .card .alert-info').closest('.card').hide();
            
            // إظهار مؤشر التحميل للحقول المخصصة
            if (customFieldsContainer.length === 0) {
                $('.col-lg-8').append('<div id="customFieldsContainer"></div>');
            }
            $('#customFieldsContainer').html('<div class="card"><div class="card-body text-center"><i class="fas fa-spinner fa-spin"></i> جاري تحميل الحقول المخصصة...</div></div>');
            
            // جلب الحقول المخصصة عبر AJAX
            $.ajax({
                url: '{{ route("admin.documents.custom-fields") }}',
                method: 'GET',
                data: { section_id: sectionId },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success && response.fields.length > 0) {
                        let fieldsHtml = '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">حقول الوثيقة</h5></div><div class="card-body">';
                        
                        response.fields.forEach(function(field) {
                            fieldsHtml += generateFieldHtml(field);
                        });
                        
                        fieldsHtml += '</div></div>';
                        $('#customFieldsContainer').html(fieldsHtml);

                        if (window.AdminDateSelects && typeof window.AdminDateSelects.initAll === 'function') {
                            window.AdminDateSelects.initAll(document.getElementById('customFieldsContainer'));
                        }
                        
                        // إعادة تهيئة TinyMCE للحقول الجديدة
                        if (typeof initCustomFieldsTinyMCE === 'function') {
                            initCustomFieldsTinyMCE();
                        }
                    } else {
                        $('#customFieldsContainer').html('<div class="card mb-3"><div class="card-body"><div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>لا توجد حقول مخصصة في هذا القسم</strong><br>يرجى إضافة حقول مخصصة للقسم المحدد من إعدادات الأقسام أو اختيار قسم آخر.</div></div></div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    $('#customFieldsContainer').html('<div class="card mb-3"><div class="card-body"><div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> حدث خطأ في تحميل الحقول المخصصة<br><small>تفاصيل الخطأ: ' + xhr.status + ' - ' + xhr.statusText + '</small></div></div></div>');
                 }
             });
        } else {
            // إظهار رسالة التحذير الأصلية عند عدم اختيار قسم
            $('.col-lg-8 .card .alert-info').closest('.card').show();
            $('#customFieldsContainer').empty();
        }
    });
    
    // معاينة الصورة
    $('#featured_image').on('change', function() {
        const file = this.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    // معالجة إرسال النموذج
    $('#documentForm').on('submit', function(e) {
        const action = $(document.activeElement).val();
        
        if (action === 'publish') {
            $('#is_published').val('1');
        } else if (action === 'draft') {
            $('#is_published').val('0');
        }
    });

});

function addDocumentFileRow() {
    const container = document.getElementById('document-files-container');
    const row = document.createElement('div');
    row.className = 'document-file-row border rounded p-3 mb-3';
    row.innerHTML = `
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">الملف</label>
                <input type="file" class="form-control document-file-input" name="document_files[]" onchange="updateDocumentFileDisplayName(this)">
            </div>
            <div class="col-md-5">
                <label class="form-label">اسم العرض للزوار</label>
                <input type="text" class="form-control" name="file_display_names[]" placeholder="اسم الملف كما سيظهر للزوار">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeDocumentFileRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(row);
}

function removeDocumentFileRow(btn) {
    const container = document.getElementById('document-files-container');
    const rows = container.querySelectorAll('.document-file-row');
    if (rows.length <= 1) {
        const row = btn.closest('.document-file-row');
        row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
        return;
    }
    btn.closest('.document-file-row').remove();
}

function updateDocumentFileDisplayName(fileInput) {
    const row = fileInput.closest('.document-file-row');
    const nameInput = row.querySelector('input[name="file_display_names[]"]');
    if (fileInput.files && fileInput.files[0] && nameInput && !nameInput.value) {
        const fileName = fileInput.files[0].name;
        nameInput.value = fileName.replace(/\.[^/.]+$/, '');
    }
}

// إنشاء HTML للحقول المخصصة
function generateFieldHtml(field) {
    let fieldHtml = `<div class="mb-3">
        <label for="custom_field_${field.id}" class="form-label">
            ${field.label}
            ${field.is_required ? '<span class="text-danger">*</span>' : ''}
        </label>`;
    
    switch(field.type) {
        case 'text':
            fieldHtml += `<input type="text" class="form-control" 
                id="custom_field_${field.id}" 
                name="custom_fields[${field.id}]" 
                placeholder="${field.placeholder || ''}" 
                ${field.is_required ? 'required' : ''}>`;
            break;
            
        case 'textarea':
            fieldHtml += `<textarea class="form-control" 
                id="custom_field_${field.id}" 
                name="custom_fields[${field.id}]" 
                rows="4" 
                placeholder="${field.placeholder || ''}" 
                ${field.is_required ? 'required' : ''}></textarea>`;
            break;
            
        case 'number':
            fieldHtml += `<input type="number" class="form-control" 
                id="custom_field_${field.id}" 
                name="custom_fields[${field.id}]" 
                placeholder="${field.placeholder || ''}" 
                ${field.is_required ? 'required' : ''}>`;
            break;
            
        case 'date':
            if (window.AdminDateSelects && typeof window.AdminDateSelects.buildHtml === 'function') {
                fieldHtml += window.AdminDateSelects.buildHtml(field.id, {
                    required: !!field.is_required,
                    name: `custom_fields[${field.id}]`
                });
            } else {
                fieldHtml += `<input type="date" class="form-control"
                    id="custom_field_${field.id}"
                    name="custom_fields[${field.id}]"
                    ${field.is_required ? 'required' : ''}>`;
            }
            break;
            
        case 'select':
            fieldHtml += `<select class="form-select" 
                id="custom_field_${field.id}" 
                name="custom_fields[${field.id}]" 
                ${field.is_required ? 'required' : ''}>
                <option value="">اختر...</option>`;
            if (field.options && field.options.length > 0) {
                field.options.forEach(function(option) {
                    fieldHtml += `<option value="${option}">${option}</option>`;
                });
            }
            fieldHtml += '</select>';
            break;
            
        case 'multiselect':
            fieldHtml += `<select class="form-select" 
                id="custom_field_${field.id}" 
                name="custom_fields[${field.id}][]" 
                multiple 
                ${field.is_required ? 'required' : ''}>`;
            if (field.options && field.options.length > 0) {
                field.options.forEach(function(option) {
                    fieldHtml += `<option value="${option}">${option}</option>`;
                });
            }
            fieldHtml += '</select>';
            break;

        case 'file':
            fieldHtml += `<input type="file" class="form-control" 
                id="custom_field_${field.id}" 
                name="custom_fields[${field.id}][]" 
                multiple 
                ${field.is_required ? 'required' : ''}>
                <div class="form-text">يمكنك اختيار عدة ملفات</div>`;
            break;
    }
    
    if (field.help_text) {
        fieldHtml += `<div class="form-text">${field.help_text}</div>`;
    }
    
    fieldHtml += '</div>';
    return fieldHtml;
}

// إزالة معاينة الصورة
function removeImagePreview() {
    $('#featured_image').val('');
    $('#imagePreview').hide();
    $('#previewImg').attr('src', '');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endpush

@push('scripts')
<script src="{{ asset('js/admin-date-selects.js') }}"></script>
<script src="{{ asset('dashboard/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('js/admin-tinymce.js') }}"></script>
<script src="{{ asset('js/custom-fields-tinymce.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof initAdminTinyMCE === 'function') {
        initAdminTinyMCE('#content', { language: 'ar', directionality: 'rtl' });
    }
});
</script>
@endpush

@push('styles')
<style>
.ck-editor__editable {
    min-height: 300px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.text-danger {
    color: #dc3545 !important;
}

.btn-group .btn {
    border-radius: 0.375rem;
}

.btn-group .btn:not(:last-child) {
    margin-right: 0.5rem;
}

#imagePreview {
    text-align: center;
}

#imagePreview img {
    border: 2px solid #dee2e6;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.invalid-feedback {
    display: block;
}
</style>
@endpush