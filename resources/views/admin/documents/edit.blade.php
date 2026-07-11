@extends('admin.layouts.app')

@section('title', 'تعديل الوثيقة: ' . $document->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تعديل الوثيقة: {{ $document->title }}</h3>
                    <div>
                        <a href="{{ route('admin.documents.show', $document) }}" class="btn btn-info me-2">
                            <i class="fas fa-eye"></i> عرض
                        </a>
                        <a href="{{ route('admin.documents.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('admin.documents.update', $document) }}" method="POST" enctype="multipart/form-data" id="documentForm">
                    @csrf
                    @method('PUT')
                    
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
                                                   id="title" name="title" value="{{ old('title', $document->title) }}" 
                                                   placeholder="أدخل عنوان الوثيقة" required>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">سيتم استخدام هذا العنوان في نتائج البحث وإنشاء الرابط</div>
                                        </div>
                                        @include('admin.partials.keyword-picker', [
                                            'keywordScope' => 'document',
                                            'selectedKeywords' => $document->keywords,
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
                                                        $fieldValue = $document->fieldValues->where('field_id', $field->id)->first();
                                                        $currentValue = old('custom_fields.' . $field->id, $fieldValue ? $fieldValue->value : '');
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
                                                                $selectedValues = is_array($currentValue) ? $currentValue : (json_decode($currentValue, true) ?: []);
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
                                                            @php
                                                                $existingFiles = [];
                                                                if ($currentValue) {
                                                                    $decoded = json_decode($currentValue, true);
                                                                    $existingFiles = is_array($decoded) ? $decoded : [$currentValue];
                                                                }
                                                            @endphp
                                                            @if(count($existingFiles) > 0)
                                                                <div class="mb-2">
                                                                    <small class="text-muted d-block mb-1">الملفات الحالية:</small>
                                                                    @foreach($existingFiles as $ef)
                                                                        <a href="{{ asset('storage/' . $ef) }}" target="_blank" class="btn btn-sm btn-outline-primary me-1 mb-1">
                                                                            <i class="fas fa-download"></i> {{ basename($ef) }}
                                                                        </a>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            <input type="file" class="form-control"
                                                                   id="custom_field_{{ $field->id }}"
                                                                   name="custom_fields[{{ $field->id }}][]"
                                                                   multiple>
                                                            <div class="form-text">يمكنك رفع عدة ملفات — اتركه فارغاً للاحتفاظ بالملفات الحالية</div>
                                                            @break
                                                    @endswitch
                                                    
                                                    @if($field->help_text)
                                                        <div class="form-text">{{ $field->help_text }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                لا توجد حقول مخصصة للقسم المحدد. يمكنك إضافة حقول مخصصة من إعدادات الأقسام.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
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
                                                      placeholder="أدخل محتوى الوثيقة" required>{{ old('content', $document->content) }}</textarea>
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
                                            <label for="section_id" class="form-label">القسم <span class="text-danger">*</span></label>
                                            <select class="form-select @error('section_id') is-invalid @enderror" 
                                                    id="section_id" name="section_id" required>
                                                <option value="">اختر القسم</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" 
                                                            {{ old('section_id', $document->section_id) == $section->id ? 'selected' : '' }}>
                                                        {{ $section->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- حالة النشر -->
                                        <div class="mb-3">
                                            <label for="is_published" class="form-label">حالة النشر</label>
                                            <select class="form-select @error('is_published') is-invalid @enderror" 
                                                    id="is_published" name="is_published">
                                                <option value="0" {{ old('is_published', $document->is_published) == '0' ? 'selected' : '' }}>مسودة</option>
                                                <option value="1" {{ old('is_published', $document->is_published) == '1' ? 'selected' : '' }}>منشور</option>
                                            </select>
                                            @error('is_published')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- تاريخ النشر -->
                                        <div class="mb-3" id="publishedAtContainer" style="{{ old('is_published', $document->is_published) == '1' ? '' : 'display: none;' }}">
                                            <label for="published_at" class="form-label">تاريخ النشر</label>
                                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" 
                                                   id="published_at" name="published_at" 
                                                   value="{{ old('published_at', $document->published_at ? $document->published_at->format('Y-m-d\TH:i') : '') }}">
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
                                                       {{ old('is_featured', $document->is_featured) ? 'checked' : '' }}>
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
                                                   id="sort_order" name="sort_order"
                                                   value="{{ old('sort_order', $document->sort_order ?? 0) }}" min="0">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">الأرقام الأصغر تظهر أولاً</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">إحصائيات الوثيقة</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h4 class="text-primary mb-1">{{ number_format($document->views_count) }}</h4>
                                                    <small class="text-muted">مشاهدة</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="text-info mb-1">{{ $document->fieldValues->count() }}</h4>
                                                <small class="text-muted">حقل مخصص</small>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">الكاتب:</small>
                                            <span class="fw-bold">{{ $document->user->name ?? 'غير محدد' }}</span>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">تاريخ الإنشاء:</small>
                                            <span>{{ $document->created_at->format('Y-m-d H:i') }}</span>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">آخر تحديث:</small>
                                            <span>{{ $document->updated_at->format('Y-m-d H:i') }}</span>
                                        </div>
                                        
                                        @if($document->published_at)
                                            <div class="mb-2">
                                                <small class="text-muted">تاريخ النشر:</small>
                                                <span>{{ $document->published_at->format('Y-m-d H:i') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- الملفات المرفقة -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">الملفات المرفقة</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- الملفات الحالية -->
                                        @if($document->files->count() > 0)
                                            <div class="mb-4">
                                                <h6>الملفات الحالية:</h6>
                                                <div class="row">
                                                    @foreach($document->files as $file)
                                                        <div class="col-md-6 mb-3">
                                                            <div class="border rounded p-3 d-flex align-items-center">
                                                                <div class="me-3">
                                                                    <i class="{{ $file->getIcon() }} fa-2x text-primary"></i>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <h6 class="mb-1">{{ $file->display_name }}</h6>
                                                                    <small class="text-muted d-block">{{ $file->original_name }}</small>
                                                                    <small class="text-muted">{{ $file->getFormattedSize() }}</small>
                                                                </div>
                                                                <div>
                                                                    <a href="{{ route('admin.documents.files.download', $file) }}" class="btn btn-outline-primary btn-sm me-1">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteFile({{ $file->id }})">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- رفع ملفات جديدة -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <label class="form-label mb-0">رفع ملفات جديدة</label>
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addDocumentFileRow()">
                                                <i class="fas fa-plus"></i> إضافة ملف
                                            </button>
                                        </div>
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
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.documents.index') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                                
                                @if($document->fieldValues->count() > 0 || $document->views_count > 0)
                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                        <i class="fas fa-trash"></i> حذف الوثيقة
                                    </button>
                                @endif
                            </div>
                            
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

<!-- مودال تأكيد الحذف -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تأكيد حذف الوثيقة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف الوثيقة "<strong>{{ $document->title }}</strong>"؟</p>
                <div class="alert alert-warning">
                    <h6>تحذير:</h6>
                    <ul class="mb-0">
                        <li>سيتم حذف جميع البيانات المرتبطة بهذه الوثيقة</li>
                        <li>سيتم حذف جميع قيم الحقول المخصصة</li>
                        <li>هذا الإجراء لا يمكن التراجع عنه</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.documents.destroy', $document) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> حذف نهائياً
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script>
$(document).ready(function() {
    
    // إظهار/إخفاء تاريخ النشر
    $('#is_published').on('change', function() {
        if ($(this).val() == '1') {
            $('#publishedAtContainer').show();
        } else {
            $('#publishedAtContainer').hide();
        }
    });
    
    // معاينة الصورة الجديدة
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

// إزالة الصورة الحالية
function removeCurrentImage() {
    if (confirm('هل أنت متأكد من إزالة الصورة الحالية؟')) {
        $('#remove_image').val('1');
        $(event.target).closest('.mb-3').hide();
    }
}

// إزالة معاينة الصورة الجديدة
function removeImagePreview() {
    $('#featured_image').val('');
    $('#imagePreview').hide();
    $('#previewImg').attr('src', '');
}

// تأكيد حذف الوثيقة
function confirmDelete() {
    $('#deleteModal').modal('show');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function deleteFile(fileId) {
    if (confirm('هل أنت متأكد من حذف هذا الملف؟')) {
        fetch(`/admin/documents/files/${fileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ أثناء حذف الملف');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء حذف الملف');
        });
    }
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

#imagePreview img,
.card-body img {
    border: 2px solid #dee2e6;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.invalid-feedback {
    display: block;
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}

.fw-bold {
    font-weight: 600 !important;
}
</style>
@endpush