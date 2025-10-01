@extends('admin.layout')

@section('title', 'تعديل القسم: ' . $category->name . ' - MK Snow')
@section('page-title', 'تعديل القسم: ' . $category->name)

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    تعديل القسم: {{ $category->name }}
                </h5>
                <div>
                    <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-eye me-2"></i>
                        عرض
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
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
                                   value="{{ old('name_ar', $category->name_ar) }}" 
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
                                   value="{{ old('name_en', $category->name_en) }}" 
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
                            الرابط المختصر
                        </label>
                        <input type="text" 
                               class="form-control @error('slug') is-invalid @enderror" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug', $category->slug) }}" 
                               placeholder="category-slug">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            الرابط الحالي: <strong>{{ $category->slug }}</strong>
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
                                      placeholder="أدخل وصف القسم بالعربية (اختياري)">{{ old('description_ar', $category->description_ar) }}</textarea>
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
                                      placeholder="Enter category description in English (optional)">{{ old('description_en', $category->description_en) }}</textarea>
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
                                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    نشط (سيظهر في الموقع)
                                </label>
                            </div>
                        </div>
                        
                        <!-- Show in Menu -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="fas fa-bars me-1"></i>
                                إعدادات القائمة الرئيسية
                            </label>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="show_in_menu" 
                                       name="show_in_menu" 
                                       value="1" 
                                       {{ old('show_in_menu', $category->show_in_menu) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_in_menu">
                                    إظهار في القائمة الرئيسية (عام)
                                </label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="show_in_menu_ar" 
                                       name="show_in_menu_ar" 
                                       value="1" 
                                       {{ old('show_in_menu_ar', $category->show_in_menu_ar ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_in_menu_ar">
                                    إظهار في القائمة العربية
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="show_in_menu_en" 
                                       name="show_in_menu_en" 
                                       value="1" 
                                       {{ old('show_in_menu_en', $category->show_in_menu_en ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_in_menu_en">
                                    إظهار في القائمة الإنجليزية
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
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
                                   value="{{ old('sort_order', $category->sort_order) }}" 
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
                        
                        <!-- Menu Order -->
                        <div class="col-md-6 mb-3">
                            <label for="menu_order" class="form-label">
                                <i class="fas fa-sort me-1"></i>
                                ترتيب القائمة (عام)
                            </label>
                            <input type="number" 
                                   class="form-control @error('menu_order') is-invalid @enderror" 
                                   id="menu_order" 
                                   name="menu_order" 
                                   value="{{ old('menu_order', $category->menu_order) }}" 
                                   min="0" 
                                   placeholder="0">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                ترتيب ظهور القسم في القائمة الرئيسية (الأرقام الأصغر تظهر أولاً)
                            </div>
                            @error('menu_order')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Menu Order Arabic -->
                        <div class="col-md-6 mb-3">
                            <label for="menu_order_ar" class="form-label">
                                <i class="fas fa-sort me-1"></i>
                                ترتيب القائمة العربية
                            </label>
                            <input type="number" 
                                   class="form-control @error('menu_order_ar') is-invalid @enderror" 
                                   id="menu_order_ar" 
                                   name="menu_order_ar" 
                                   value="{{ old('menu_order_ar', $category->menu_order_ar) }}" 
                                   min="0" 
                                   placeholder="0">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                ترتيب خاص بالقائمة العربية
                            </div>
                            @error('menu_order_ar')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <!-- Menu Order English -->
                        <div class="col-md-6 mb-3">
                            <label for="menu_order_en" class="form-label">
                                <i class="fas fa-sort me-1"></i>
                                ترتيب القائمة الإنجليزية
                            </label>
                            <input type="number" 
                                   class="form-control @error('menu_order_en') is-invalid @enderror" 
                                   id="menu_order_en" 
                                   name="menu_order_en" 
                                   value="{{ old('menu_order_en', $category->menu_order_en) }}" 
                                   min="0" 
                                   placeholder="0">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                ترتيب خاص بالقائمة الإنجليزية
                            </div>
                            @error('menu_order_en')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Category Statistics -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-chart-bar me-2"></i>
                            إحصائيات القسم
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>عدد المواضيع:</strong> {{ $category->posts()->count() }}
                            </div>
                            <div class="col-md-4">
                                <strong>المواضيع المنشورة:</strong> {{ $category->posts()->where('status', 'published')->count() }}
                            </div>
                            <div class="col-md-4">
                                <strong>تاريخ الإنشاء:</strong> {{ $category->created_at->format('Y-m-d') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        
                        <div>
                            <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-outline-info me-2">
                                <i class="fas fa-eye me-2"></i>
                                عرض
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                حفظ التغييرات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-generate slug from English name (only if current slug matches the pattern)
document.getElementById('name_en').addEventListener('input', function() {
    const nameEn = this.value;
    const slugField = document.getElementById('slug');
    const originalSlug = '{{ $category->slug }}';
    
    // Only auto-update if the current slug seems to be auto-generated
    if (nameEn && slugField.value === originalSlug) {
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