@extends('admin.layouts.app')

@section('title', 'إضافة قسم جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إضافة قسم جديد</h3>
                    <a href="{{ route('admin.document-sections.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> العودة للقائمة
                    </a>
                </div>
                
                <form action="{{ route('admin.document-sections.store') }}" method="POST">
                    @csrf
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
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="مثال: القوانين، القرارات، أحكام النقض" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="slug" class="form-label">
                                        <i class="fas fa-link me-1"></i>
                                        الرابط المختصر (slug)
                                    </label>
                                    <input type="text"
                                           class="form-control @error('slug') is-invalid @enderror"
                                           id="slug"
                                           name="slug"
                                           value="{{ old('slug') }}"
                                           placeholder="judgments-of-the-court-of-cassation">
                                    <div class="form-text">
                                        اتركه فارغًا ليُنشأ تلقائيًا من اسم القسم، أو اكتبه يدويًا.
                                    </div>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">وصف القسم</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" 
                                              placeholder="وصف مختصر عن محتوى هذا القسم">{{ old('description') }}</textarea>
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
                                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}" 
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
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    تفعيل القسم
                                                </label>
                                            </div>
                                            <div class="form-text">الأقسام غير المفعلة لن تظهر في الواجهة الأمامية</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>ملاحظة:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>بعد إنشاء القسم يمكنك إضافة الحقول المخصصة له</li>
                                        <li>يمكن تعديل جميع الإعدادات لاحقاً</li>
                                        <li>اختر اسماً واضحاً ومميزاً للقسم</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="admin-form-actions">
                            <a href="{{ route('admin.document-sections.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ القسم
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
    function slugify(value) {
        return (value || '')
            .toLowerCase()
            .replace(/[^\u0600-\u06FF\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    let slugTouched = $('#slug').val().trim() !== '';

    $('#slug').on('input', function() {
        slugTouched = $(this).val().trim() !== '';
    });

    $('#name').on('input', function() {
        if (!slugTouched) {
            $('#slug').val(slugify($(this).val()));
        }
    });
    
    // التحقق من صحة النموذج قبل الإرسال
    $('form').on('submit', function(e) {
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

.alert-info {
    border-left: 4px solid #17a2b8;
}

#slug-preview {
    font-size: 0.875rem;
}
</style>
@endpush