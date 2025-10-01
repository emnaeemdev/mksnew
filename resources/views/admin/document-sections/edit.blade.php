@extends('admin.layouts.app')

@section('title', 'تعديل القسم: ' . $section->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تعديل القسم: {{ $section->name }}</h3>
                    <div>
                        <a href="{{ route('admin.document-sections.show', $section) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> عرض
                        </a>
                        <a href="{{ route('admin.document-sections.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> العودة
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('admin.document-sections.update', $section) }}" method="POST">
                    @csrf
                    @method('PUT')
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
                                <div class="mb-3">
                                    <label for="name" class="form-label">اسم القسم <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $section->name) }}" 
                                           placeholder="مثال: القوانين، القرارات، أحكام النقض" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">الرابط الحالي: <strong>{{ $section->slug }}</strong></div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">وصف القسم</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" 
                                              placeholder="وصف مختصر عن محتوى هذا القسم">{{ old('description', $section->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">إعدادات القسم</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">ترتيب العرض</label>
                                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                                   id="sort_order" name="sort_order" value="{{ old('sort_order', $section->sort_order) }}" 
                                                   min="0" placeholder="1">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">الأقسام ذات الأرقام الأقل تظهر أولاً</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="is_active" name="is_active" value="1" 
                                                       {{ old('is_active', $section->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    تفعيل القسم
                                                </label>
                                            </div>
                                            <div class="form-text">الأقسام غير المفعلة لن تظهر في الواجهة الأمامية</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="show_in_menu" name="show_in_menu" value="1" 
                                                       {{ old('show_in_menu', $section->show_in_menu) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_menu">
                                                    إظهار في القائمة الرئيسية (عام)
                                                </label>
                                            </div>
                                            <div class="form-text">سيظهر القسم في قائمة الوثائق الرئيسية</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="show_in_menu_ar" name="show_in_menu_ar" value="1" 
                                                       {{ old('show_in_menu_ar', $section->show_in_menu_ar) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_menu_ar">
                                                    إظهار في القائمة العربية
                                                </label>
                                            </div>
                                            <div class="form-text">سيظهر القسم في القائمة عند عرض الموقع باللغة العربية</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="show_in_menu_en" name="show_in_menu_en" value="1" 
                                                       {{ old('show_in_menu_en', $section->show_in_menu_en) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_menu_en">
                                                    إظهار في القائمة الإنجليزية
                                                </label>
                                            </div>
                                            <div class="form-text">سيظهر القسم في القائمة عند عرض الموقع باللغة الإنجليزية</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="menu_order" class="form-label">ترتيب القائمة (عام)</label>
                                            <input type="number" class="form-control @error('menu_order') is-invalid @enderror" 
                                                   id="menu_order" name="menu_order" value="{{ old('menu_order', $section->menu_order) }}" 
                                                   min="0" placeholder="0">
                                            @error('menu_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">ترتيب ظهور القسم في القائمة الرئيسية</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="menu_order_ar" class="form-label">ترتيب القائمة العربية</label>
                                            <input type="number" class="form-control @error('menu_order_ar') is-invalid @enderror" 
                                                   id="menu_order_ar" name="menu_order_ar" value="{{ old('menu_order_ar', $section->menu_order_ar) }}" 
                                                   min="0" placeholder="0">
                                            @error('menu_order_ar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">ترتيب ظهور القسم في القائمة العربية</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="menu_order_en" class="form-label">ترتيب القائمة الإنجليزية</label>
                                            <input type="number" class="form-control @error('menu_order_en') is-invalid @enderror" 
                                                   id="menu_order_en" name="menu_order_en" value="{{ old('menu_order_en', $section->menu_order_en) }}" 
                                                   min="0" placeholder="0">
                                            @error('menu_order_en')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">ترتيب ظهور القسم في القائمة الإنجليزية</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="is_dropdown" name="is_dropdown" value="1" 
                                                       {{ old('is_dropdown', $section->is_dropdown) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_dropdown">
                                                    عرض كقائمة منسدلة
                                                </label>
                                            </div>
                                            <div class="form-text">سيظهر القسم كقائمة منسدلة في المنيو</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="dropdown_title" class="form-label">عنوان القائمة المنسدلة</label>
                                            <input type="text" class="form-control @error('dropdown_title') is-invalid @enderror" 
                                                   id="dropdown_title" name="dropdown_title" 
                                                   value="{{ old('dropdown_title', $section->dropdown_title) }}" 
                                                   placeholder="مثال: الأحكام">
                                            @error('dropdown_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">العنوان الذي سيظهر في القائمة المنسدلة (اختياري)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">إحصائيات القسم</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h4 class="text-primary mb-1">{{ $section->documents_count ?? 0 }}</h4>
                                                    <small class="text-muted">الوثائق</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="text-success mb-1">{{ $section->custom_fields_count ?? 0 }}</h4>
                                                <small class="text-muted">الحقول المخصصة</small>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('admin.document-custom-fields.index', ['section' => $section->id]) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-cogs"></i> إدارة الحقول المخصصة
                                            </a>
                                            <a href="{{ route('admin.documents.index', ['section' => $section->id]) }}" 
                                               class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-file-alt"></i> إدارة الوثائق
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>تنبيه:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>تغيير اسم القسم سيؤثر على الرابط</li>
                                        <li>إلغاء تفعيل القسم سيخفيه من الواجهة الأمامية</li>
                                        <li>تم إنشاء القسم: {{ $section->created_at->format('Y/m/d H:i') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.document-sections.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                                @if($section->documents_count == 0)
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash"></i> حذف القسم
                                    </button>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($section->documents_count == 0)
<!-- Modal حذف القسم -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> تأكيد حذف القسم
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">هل أنت متأكد من حذف القسم <strong>"{{ $section->name }}"</strong>؟</p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <strong>تنبيه:</strong> هذا الإجراء لا يمكن التراجع عنه وسيتم حذف:
                    <ul class="mb-0 mt-2">
                        <li>القسم نفسه</li>
                        <li>جميع الحقول المخصصة المرتبطة به ({{ $section->custom_fields_count ?? 0 }} حقل)</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> إلغاء
                </button>
                <form action="{{ route('admin.document-sections.destroy', $section) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> نعم، احذف القسم
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // تحديث معاينة الرابط عند تغيير الاسم
    $('#name').on('input', function() {
        const name = $(this).val();
        const slug = name.toLowerCase()
            .replace(/[^\u0600-\u06FF\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        
        if (slug && slug !== '{{ $section->slug }}') {
            if (!$('#slug-preview').length) {
                $('#name').siblings('.form-text').after('<div id="slug-preview" class="form-text text-warning mt-1"></div>');
            }
            $('#slug-preview').html('<i class="fas fa-link"></i> الرابط الجديد: <strong>' + slug + '</strong>');
        } else {
            $('#slug-preview').remove();
        }
    });
    
    // التحقق من صحة النموذج قبل الإرسال
    $('form').not('#deleteModal form').on('submit', function(e) {
        const name = $('#name').val().trim();
        
        if (name.length < 2) {
            e.preventDefault();
            alert('يجب أن يكون اسم القسم أكثر من حرفين');
            $('#name').focus();
            return false;
        }
        
        // إظهار مؤشر التحميل
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');
    });
    
    // تأكيد حذف القسم
    $('#deleteModal form').on('submit', function(e) {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin"></i> جاري الحذف...');
    });
});
</script>
@endpush

@push('styles')
<style>
.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

.card.bg-light {
    border: 1px solid #dee2e6;
}

.alert-warning {
    border-left: 4px solid #ffc107;
}

#slug-preview {
    font-size: 0.875rem;
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}
</style>
@endpush