@extends('admin.layout')

@section('title', 'إضافة قسم جديد - MK Snow')
@section('page-title', 'إضافة قسم جديد')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    إضافة قسم جديد
                </h5>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    العودة للقائمة
                </a>
            </div>
            
            <div class="card-body">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <!-- Arabic Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name_ar" class="form-label">
                                <i class="fas fa-tag me-1"></i>
                                الاسم بالعربية <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_ar') is-invalid @enderror" 
                                   id="name_ar" 
                                   name="name_ar" 
                                   value="{{ old('name_ar') }}" 
                                   placeholder="أدخل اسم القسم بالعربية"
                                   required>
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- English Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name_en" class="form-label">
                                <i class="fas fa-tag me-1"></i>
                                الاسم بالإنجليزية <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_en') is-invalid @enderror" 
                                   id="name_en" 
                                   name="name_en" 
                                   value="{{ old('name_en') }}" 
                                   placeholder="Enter category name in English"
                                   required>
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Slug -->
                    <div class="mb-3">
                        <label for="slug" class="form-label">
                            <i class="fas fa-link me-1"></i>
                            الرابط المختصر (اختياري)
                        </label>
                        <input type="text" 
                               class="form-control @error('slug') is-invalid @enderror" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug') }}" 
                               placeholder="سيتم إنشاؤه تلقائياً من الاسم الإنجليزي">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            إذا تُرك فارغاً، سيتم إنشاؤه تلقائياً من الاسم الإنجليزي
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <!-- Arabic Description -->
                        <div class="col-md-6 mb-3">
                            <label for="description_ar" class="form-label">
                                <i class="fas fa-align-left me-1"></i>
                                الوصف بالعربية
                            </label>
                            <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                      id="description_ar" 
                                      name="description_ar" 
                                      rows="4" 
                                      placeholder="أدخل وصف القسم بالعربية (اختياري)">{{ old('description_ar') }}</textarea>
                            @error('description_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- English Description -->
                        <div class="col-md-6 mb-3">
                            <label for="description_en" class="form-label">
                                <i class="fas fa-align-left me-1"></i>
                                الوصف بالإنجليزية
                            </label>
                            <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                      id="description_en" 
                                      name="description_en" 
                                      rows="4" 
                                      placeholder="Enter category description in English (optional)">{{ old('description_en') }}</textarea>
                            @error('description_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Active Status -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="fas fa-toggle-on me-1"></i>
                                حالة القسم
                            </label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    نشط (سيظهر في الموقع)
                                </label>
                            </div>
                        </div>
                        
                        <!-- Sort Order -->
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">
                                <i class="fas fa-sort-numeric-down me-1"></i>
                                ترتيب العرض
                            </label>
                            <input type="number" 
                                   class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', 0) }}" 
                                   min="0" 
                                   placeholder="0">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                الرقم الأصغر يظهر أولاً
                            </div>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            حفظ القسم
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-generate slug from English name
document.getElementById('name_en').addEventListener('input', function() {
    const nameEn = this.value;
    const slugField = document.getElementById('slug');
    
    if (nameEn && !slugField.value) {
        const slug = nameEn
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        
        slugField.value = slug;
    }
});
</script>
@endsection