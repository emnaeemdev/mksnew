@extends('admin.layouts.app')

@section('title', 'إضافة وثيقة جديدة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إضافة وثيقة جديدة</h3>
                    <a href="{{ route('admin.documents.index') }}" class="btn btn-secondary">
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
                                                            <input type="date" class="form-control" 
                                                                   id="custom_field_{{ $field->id }}" 
                                                                   name="custom_fields[{{ $field->id }}]" 
                                                                   value="{{ $currentValue }}" 
                                                                   {{ $field->is_required ? 'required' : '' }}>
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
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">الملفات المرفقة</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="document_files" class="form-label">رفع ملفات</label>
                                            <input type="file" class="form-control" id="document_files" name="document_files[]" multiple>
                                            <div class="form-text">يمكنك رفع عدة ملفات في نفس الوقت</div>
                                        </div>
                                        
                                        <!-- معاينة الملفات المحددة -->
                                        <div id="files-preview" class="mt-3" style="display: none;">
                                            <h6>الملفات المحددة:</h6>
                                            <div id="files-list"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.documents.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                            
                            <div>
                                <button type="submit" name="action" value="draft" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-save"></i> حفظ كمسودة
                                </button>
                                <button type="submit" name="action" value="publish" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> حفظ ونشر
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
            fieldHtml += `<input type="date" class="form-control" 
                id="custom_field_${field.id}" 
                name="custom_fields[${field.id}]" 
                ${field.is_required ? 'required' : ''}>`;
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
</script>
@endpush

@push('scripts')
<script src="{{ asset('dashboard/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('js/custom-fields-tinymce.js') }}"></script>
<script>
// Initialize TinyMCE for main content field
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#content',
        height: 400,
        language: 'ar',
        directionality: 'rtl',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount',
            'nonbreaking', 'save', 'directionality'
        ],
        toolbar: 'undo redo | blocks fontsize | bold italic underline | ' +
                'alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | ' +
                'forecolor backcolor removeformat | charmap | image media | ' +
                'fullscreen preview | link | ltr rtl | help',
        menubar: false,
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; direction: rtl; }',
        font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt',
        block_formats: 'فقرة=p; عنوان 1=h1; عنوان 2=h2; عنوان 3=h3; عنوان 4=h4; عنوان 5=h5; عنوان 6=h6; مُنسق مسبقاً=pre',
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
    });
        
        // معالجة رفع الملفات
        $('#document_files').on('change', function() {
            const files = this.files;
            const filesPreview = $('#files-preview');
            const filesList = $('#files-list');
            
            if (files.length > 0) {
                filesPreview.show();
                filesList.empty();
                
                Array.from(files).forEach((file, index) => {
                    const fileItem = $(`
                        <div class="file-item border rounded p-3 mb-2" data-index="${index}">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <strong>الملف الأصلي:</strong><br>
                                    <small class="text-muted">${file.name}</small><br>
                                    <small class="text-muted">${formatFileSize(file.size)}</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">اسم العرض:</label>
                                    <input type="text" class="form-control file-display-name" 
                                           name="file_display_names[]" 
                                           value="${file.name.replace(/\.[^/.]+$/, '')}" 
                                           placeholder="أدخل اسم العرض للملف">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-file">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);
                    filesList.append(fileItem);
                });
            } else {
                filesPreview.hide();
            }
        });
        
        // إزالة ملف من القائمة
        $(document).on('click', '.remove-file', function() {
            const fileItem = $(this).closest('.file-item');
            const index = fileItem.data('index');
            
            // إزالة العنصر من DOM
            fileItem.remove();
            
            // إعادة تعيين input الملفات
            const fileInput = $('#document_files')[0];
            const dt = new DataTransfer();
            
            Array.from(fileInput.files).forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            
            fileInput.files = dt.files;
            
            // إخفاء المعاينة إذا لم تعد هناك ملفات
            if (fileInput.files.length === 0) {
                $('#files-preview').hide();
            }
            
            // إعادة ترقيم العناصر
            $('.file-item').each(function(newIndex) {
                $(this).attr('data-index', newIndex);
            });
        });
        
        // دالة لتنسيق حجم الملف
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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