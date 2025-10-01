@extends('admin.layout')

@section('title', 'تعديل الموضوع: ' . $post->title . ' - MK Snow')
@section('page-title', 'تعديل الموضوع: ' . Str::limit($post->title, 50))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-posts.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    تعديل الموضوع: {{ Str::limit($post->title, 40) }}
                </h5>
                <div>
                    <a target="_blank" href="{{ route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id]) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-eye me-2"></i>
                        عرض
                    </a>
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Category -->
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">
                                <i class="fas fa-tag me-1"></i>
                                القسم <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" 
                                    name="category_id" 
                                    required>
                                <option value="">اختر القسم</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-flag me-1"></i>
                                حالة الموضوع <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="draft" {{ old('status', $post->status ?? 'published') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="under_review" {{ old('status', $post->status ?? 'published') == 'under_review' ? 'selected' : '' }}>قيد المراجعة</option>
                                <option value="published" {{ old('status', $post->status ?? 'published') == 'published' ? 'selected' : '' }}>منشور</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Hidden Slug Field -->
                    <input type="hidden" name="slug" value="{{ $post->slug }}">
                    
                    <!-- Language Content Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-language me-2"></i>
                                محتوى المقال
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Language Tabs -->
                            <ul class="nav nav-tabs mb-4" id="contentTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="arabic-content-tab" data-bs-toggle="tab" data-bs-target="#arabic-content" type="button" role="tab" aria-controls="arabic-content" aria-selected="true">
                                        <i class="fas fa-flag me-1"></i>
                                        المحتوى العربي
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="english-content-tab" data-bs-toggle="tab" data-bs-target="#english-content" type="button" role="tab" aria-controls="english-content" aria-selected="false">
                                        <i class="fas fa-flag me-1"></i>
                                        المحتوى الإنجليزي (اختياري)
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="contentTabsContent">
                                <!-- Arabic Content Tab -->
                                <div class="tab-pane fade show active" id="arabic-content" role="tabpanel" aria-labelledby="arabic-content-tab">
                                    <div class="row">
                                        <!-- Arabic Title -->
                                        <div class="col-md-8 mb-3">
                                            <label for="title_ar" class="form-label">
                                                <i class="fas fa-heading me-1"></i>
                                                العنوان <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('title_ar') is-invalid @enderror" 
                                                   id="title_ar" 
                                                   name="title_ar" 
                                                   value="{{ old('title_ar', $post->title_ar) }}" 
                                                   placeholder="أدخل عنوان الموضوع بالعربية"
                                                   required>
                                            @error('title_ar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Arabic Slug -->
                                        <!-- <div class="col-md-6 mb-3">
                                            <label for="slug_ar" class="form-label">
                                                <i class="fas fa-link me-1"></i>
                                                الرابط المختصر
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('slug') is-invalid @enderror" 
                                                   id="slug_ar" 
                                                   name="slug" 
                                                   value="{{ old('slug', $post->slug) }}" 
                                                   placeholder="الرابط المختصر للموضوع">
                                            @error('slug')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div> -->
                                    </div>
                                    
                                    <!-- Arabic Content -->
                                    <div class="mb-3">
                                        <label for="content_ar" class="form-label">
                                            <i class="fas fa-align-left me-1"></i>
                                            المحتوى <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control tinymce-editor @error('content_ar') is-invalid @enderror" 
                                                  id="content_ar"
                                                  name="content_ar"
                                                  rows="10"
                                                  placeholder="أدخل محتوى الموضوع بالعربية"
                                                  required>{{ old('content_ar', $post->content_ar) }}</textarea>
                                        @error('content_ar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- English Content Tab -->
                                <div class="tab-pane fade" id="english-content" role="tabpanel" aria-labelledby="english-content-tab">
                                    <!-- English Title -->
                                    <div class="mb-3">
                                        <label for="title_en" class="form-label">
                                            <i class="fas fa-heading me-1"></i>
                                            العنوان بالإنجليزية <span class="text-muted">(اختياري)</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('title_en') is-invalid @enderror" 
                                               id="title_en" 
                                               name="title_en" 
                                               value="{{ old('title_en', $post->title_en) }}" 
                                               placeholder="Enter topic title in English (optional)">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            إذا لم يتم إدخال عنوان إنجليزي، سيتم استخدام العنوان العربي في النسخة الإنجليزية
                                        </div>
                                        @error('title_en')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- English Content -->
                                    <div class="mb-3">
                                        <label for="content_en" class="form-label">
                                            <i class="fas fa-align-left me-1"></i>
                                            المحتوى بالإنجليزية <span class="text-muted">(اختياري)</span>
                                        </label>
                                        <textarea class="form-control tinymce-editor @error('content_en') is-invalid @enderror"
                                                  id="content_en"
                                                  name="content_en"
                                                  rows="10"
                                                  placeholder="Enter topic content in English (optional)">{{ old('content_en', $post->content_en) }}</textarea>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            إذا لم يتم إدخال محتوى إنجليزي، سيتم استخدام المحتوى العربي في النسخة الإنجليزية
                                        </div>
                                        @error('content_en')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Images Section -->
                    <div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-images me-2"></i>
            الصور
        </h6>
    </div>
    <div class="card-body">
        <!-- Image Tabs -->
        <ul class="nav nav-tabs mb-4" id="imageTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="arabic-images-tab" data-bs-toggle="tab" data-bs-target="#arabic-images" type="button" role="tab" aria-controls="arabic-images" aria-selected="true">
                    <i class="fas fa-flag me-1"></i>
                    الصور العربية
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="english-images-tab" data-bs-toggle="tab" data-bs-target="#english-images" type="button" role="tab" aria-controls="english-images" aria-selected="false">
                    <i class="fas fa-flag me-1"></i>
                    الصور الإنجليزية (اختياري)
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="imageTabsContent">
            <!-- Arabic Images Tab -->
            <div class="tab-pane fade show active" id="arabic-images" role="tabpanel" aria-labelledby="arabic-images-tab">
                <div class="row">
                    <!-- Featured Image Arabic (Now First) -->
                    <div class="col-md-6 mb-3">
                        <label for="featured_image_ar" class="form-label">
                            <i class="fas fa-star me-1"></i>
                           صورة الموضوع
                        </label>
                        
                        @if($post->featured_image_ar)
                            <div class="current-file">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-star text-warning me-2"></i>
                                        <strong>الصورة الحالية:</strong>
                                        <a href="{{ asset('storage/' . $post->featured_image_ar) }}" target="_blank">
                                            عرض الصورة
                                        </a>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="remove_featured_image_ar" value="1" 
                                               id="remove_featured_image_ar">
                                        <label class="form-check-label text-danger" for="remove_featured_image_ar">
                                            حذف
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <input type="file" 
                               class="form-control @error('featured_image_ar') is-invalid @enderror" 
                               id="featured_image_ar" 
                               name="featured_image_ar" 
                               accept="image/*"
                               onchange="previewImage(this, 'featured-preview-ar')">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            الحد الأقصى: 2MB، الأنواع المدعومة: JPG, PNG, GIF
                        </div>
                        <div id="featured-preview-ar" class="image-preview" style="display: none;">
                            <img src="" alt="معاينة الصورة المميزة العربية">
                        </div>
                        @error('featured_image_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Background Image Arabic (Now Second) -->
                    <div class="col-md-6 mb-3">
                        <label for="background_image_ar" class="form-label">
                            <i class="fas fa-image me-1"></i>
                            صورة الخلفية
                        </label>
                        
                        @if($post->background_image_ar)
                            <div class="current-file">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-image text-primary me-2"></i>
                                        <strong>الصورة الحالية:</strong>
                                        <a href="{{ asset('storage/' . $post->background_image_ar) }}" target="_blank">
                                            عرض الصورة
                                        </a>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="remove_background_image_ar" value="1" 
                                               id="remove_bg_image_ar">
                                        <label class="form-check-label text-danger" for="remove_bg_image_ar">
                                            حذف
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <input type="file" 
                               class="form-control @error('background_image_ar') is-invalid @enderror" 
                               id="background_image_ar" 
                               name="background_image_ar" 
                               accept="image/*"
                               onchange="previewImage(this, 'bg-preview-ar')">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            الحد الأقصى: 2MB، الأنواع المدعومة: JPG, PNG, GIF
                        </div>
                        <div id="bg-preview-ar" class="image-preview" style="display: none;">
                            <img src="" alt="معاينة صورة الخلفية العربية">
                        </div>
                        @error('background_image_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- English Images Tab -->
            <div class="tab-pane fade" id="english-images" role="tabpanel" aria-labelledby="english-images-tab">
                <div class="row">
                    <!-- Featured Image English (Now First) -->
                    <div class="col-md-6 mb-3">
                        <label for="featured_image_en" class="form-label">
                            <i class="fas fa-star me-1"></i>
                            صورة الموضوع<span class="text-muted">(اختياري)</span>
                        </label>
                        
                        @if($post->featured_image_en)
                            <div class="current-file">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-star text-warning me-2"></i>
                                        <strong>الصورة الحالية:</strong>
                                        <a href="{{ asset('storage/' . $post->featured_image_en) }}" target="_blank">
                                            عرض الصورة
                                        </a>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="remove_featured_image_en" value="1" 
                                               id="remove_featured_image_en">
                                        <label class="form-check-label text-danger" for="remove_featured_image_en">
                                            حذف
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <input type="file" 
                               class="form-control @error('featured_image_en') is-invalid @enderror" 
                               id="featured_image_en" 
                               name="featured_image_en" 
                               accept="image/*"
                               onchange="previewImage(this, 'featured-preview-en')">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            الحد الأقصى: 2MB، الأنواع المدعومة: JPG, PNG, GIF
                        </div>
                        <div id="featured-preview-en" class="image-preview" style="display: none;">
                            <img src="" alt="معاينة الصورة المميزة الإنجليزية">
                        </div>
                        @error('featured_image_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Background Image English (Now Second) -->
                    <div class="col-md-6 mb-3">
                        <label for="background_image_en" class="form-label">
                            <i class="fas fa-image me-1"></i>
                            صورة الخلفية <span class="text-muted">(اختياري)</span>
                        </label>
                        
                        @if($post->background_image_en)
                            <div class="current-file">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-image text-primary me-2"></i>
                                        <strong>الصورة الحالية:</strong>
                                        <a href="{{ asset('storage/' . $post->background_image_en) }}" target="_blank">
                                            عرض الصورة
                                        </a>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="remove_background_image_en" value="1" 
                                               id="remove_bg_image_en">
                                        <label class="form-check-label text-danger" for="remove_bg_image_en">
                                            حذف
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <input type="file" 
                               class="form-control @error('background_image_en') is-invalid @enderror" 
                               id="background_image_en" 
                               name="background_image_en" 
                               accept="image/*"
                               onchange="previewImage(this, 'bg-preview-en')">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            الحد الأقصى: 2MB، الأنواع المدعومة: JPG, PNG, GIF
                        </div>
                        <div id="bg-preview-en" class="image-preview" style="display: none;">
                            <img src="" alt="معاينة صورة الخلفية الإنجليزية">
                        </div>
                        @error('background_image_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                    
                    <!-- Multiple Files Upload Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-files me-2"></i>
                                الملفات المرفقة
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Current Files -->
                            @if($post->files && $post->files->count() > 0)
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-folder-open me-1"></i>
                                        الملفات الحالية
                                    </h6>
                                    
                                    <!-- Arabic Files -->
                                    @php
                                        $arabicFiles = $post->files->where('language', 'ar');
                                        $englishFiles = $post->files->where('language', 'en');
                                        $noLanguageFiles = $post->files->whereNull('language');
                                    @endphp
                                    
                                    @if($arabicFiles->count() > 0)
                                        <div class="mb-3">
                                            <h6 class="text-primary mb-2">
                                                <i class="fas fa-flag me-1"></i>
                                                الملفات العربية
                                            </h6>
                                            @foreach($arabicFiles as $file)
                                                <div class="current-file mb-2 border-start border-primary border-3 ps-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-file text-info me-2"></i>
                                                            <strong>{{ $file->display_name }}</strong>
                                                            <span class="text-muted">({{ $file->original_name }})</span>
                                                            <span class="badge bg-primary ms-2">عربي</span>
                                                            <a href="{{ $file->fileUrl }}" target="_blank" class="ms-2">
                                                                <i class="fas fa-download"></i> تحميل
                                                            </a>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="remove_files[]" value="{{ $file->id }}" 
                                                                   id="remove_file_{{ $file->id }}">
                                                            <label class="form-check-label text-danger" for="remove_file_{{ $file->id }}">
                                                                حذف
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if($englishFiles->count() > 0)
                                        <div class="mb-3">
                                            <h6 class="text-success mb-2">
                                                <i class="fas fa-flag me-1"></i>
                                                الملفات الإنجليزية
                                            </h6>
                                            @foreach($englishFiles as $file)
                                                <div class="current-file mb-2 border-start border-success border-3 ps-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-file text-info me-2"></i>
                                                            <strong>{{ $file->display_name }}</strong>
                                                            <span class="text-muted">({{ $file->original_name }})</span>
                                                            <span class="badge bg-success ms-2">إنجليزي</span>
                                                            <a href="{{ $file->fileUrl }}" target="_blank" class="ms-2">
                                                                <i class="fas fa-download"></i> تحميل
                                                            </a>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="remove_files[]" value="{{ $file->id }}" 
                                                                   id="remove_file_{{ $file->id }}">
                                                            <label class="form-check-label text-danger" for="remove_file_{{ $file->id }}">
                                                                حذف
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if($noLanguageFiles->count() > 0)
                                        <div class="mb-3">
                                            <h6 class="text-warning mb-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                ملفات بدون تحديد لغة
                                            </h6>
                                            @foreach($noLanguageFiles as $file)
                                                <div class="current-file mb-2 border-start border-warning border-3 ps-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-file text-info me-2"></i>
                                                            <strong>{{ $file->display_name }}</strong>
                                                            <span class="text-muted">({{ $file->original_name }})</span>
                                                            <span class="badge bg-warning text-dark ms-2">غير محدد</span>
                                                            <a href="{{ $file->fileUrl }}" target="_blank" class="ms-2">
                                                                <i class="fas fa-download"></i> تحميل
                                                            </a>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="remove_files[]" value="{{ $file->id }}" 
                                                                   id="remove_file_{{ $file->id }}">
                                                            <label class="form-check-label text-danger" for="remove_file_{{ $file->id }}">
                                                                حذف
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <hr>
                            @endif
                            
                            <!-- File Tabs -->
                            <ul class="nav nav-tabs mb-4" id="fileTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="arabic-files-tab" data-bs-toggle="tab" data-bs-target="#arabic-files" type="button" role="tab" aria-controls="arabic-files" aria-selected="true">
                                        <i class="fas fa-flag me-1"></i>
                                        الملفات العربية
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="english-files-tab" data-bs-toggle="tab" data-bs-target="#english-files" type="button" role="tab" aria-controls="english-files" aria-selected="false">
                                        <i class="fas fa-flag me-1"></i>
                                        الملفات الإنجليزية (اختياري)
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="fileTabsContent">
                                <!-- Arabic Files Tab -->
                                <div class="tab-pane fade show active" id="arabic-files" role="tabpanel" aria-labelledby="arabic-files-tab">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">
                                            <i class="fas fa-flag me-1"></i>
                                            الملفات العربية
                                        </h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addFileInput('ar')">
                                            <i class="fas fa-plus me-1"></i>
                                            إضافة ملف
                                        </button>
                                    </div>
                                    <div id="files-container-ar">
                                        <div class="file-input-group mb-3" data-index="0" data-lang="ar">
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">
                                                        <i class="fas fa-paperclip me-1"></i>
                                                        الملف
                                                    </label>
                                                    <input type="file" 
                                                           class="form-control" 
                                                           name="files_ar[0][file]" 
                                                           onchange="autoFillFileName(this, 0, 'ar')">
                                                    <div class="form-text">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        الحد الأقصى: 10MB، جميع أنواع الملفات مدعومة
                                                    </div>
                                                </div>
                                                <div class="col-md-5 mb-2">
                                                    <label class="form-label">
                                                        <i class="fas fa-tag me-1"></i>
                                                        اسم الملف للعرض
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="files_ar[0][display_name]" 
                                                           placeholder="اسم الملف كما سيظهر للزوار">
                                                </div>
                                                <div class="col-md-1 mb-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFileInput(this, 'ar')" style="display: none;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- English Files Tab -->
                                <div class="tab-pane fade" id="english-files" role="tabpanel" aria-labelledby="english-files-tab">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">
                                            <i class="fas fa-flag me-1"></i>
                                            الملفات الإنجليزية <span class="text-muted">(اختياري)</span>
                                        </h6>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addFileInput('en')">
                                            <i class="fas fa-plus me-1"></i>
                                            إضافة ملف
                                        </button>
                                    </div>
                                    <div id="files-container-en">
                                        <div class="file-input-group mb-3" data-index="0" data-lang="en">
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">
                                                        <i class="fas fa-paperclip me-1"></i>
                                                        الملف <span class="text-muted">(اختياري)</span>
                                                    </label>
                                                    <input type="file" 
                                                           class="form-control" 
                                                           name="files_en[0][file]" 
                                                           onchange="autoFillFileName(this, 0, 'en')">
                                                    <div class="form-text">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        الحد الأقصى: 10MB، جميع أنواع الملفات مدعومة
                                                    </div>
                                                </div>
                                                <div class="col-md-5 mb-2">
                                                    <label class="form-label">
                                                        <i class="fas fa-tag me-1"></i>
                                                        اسم الملف للعرض <span class="text-muted">(اختياري)</span>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="files_en[0][display_name]" 
                                                           placeholder="File name as it will appear to visitors">
                                                </div>
                                                <div class="col-md-1 mb-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFileInput(this, 'en')" style="display: none;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Display Options -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-cog me-2"></i>
                                خيارات العرض
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Language Tabs for Display Options -->
                            <ul class="nav nav-tabs mb-3" id="displayOptionsTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="display-ar-tab" data-bs-toggle="tab" data-bs-target="#display-ar" type="button" role="tab">
                                        <i class="fas fa-flag me-1"></i>
                                        خيارات العرض - العربية
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="display-en-tab" data-bs-toggle="tab" data-bs-target="#display-en" type="button" role="tab">
                                        <i class="fas fa-flag me-1"></i>
                                        Display Options - English <span class="text-muted">(Optional)</span>
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="displayOptionsTabContent">
                                <!-- Arabic Display Options -->
                                <div class="tab-pane fade show active" id="display-ar" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch" style="padding-left: 20.5em;">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_slider_ar" 
                                                       name="show_in_slider_ar" 
                                                       value="1" 
                                                       {{ old('show_in_slider_ar', $post->show_in_slider_ar) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_slider_ar">
                                                    <i class="fas fa-images me-1"></i>
                                                    عرض في السلايدر
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch" style="padding-left: 18.5em;">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_releases_ar" 
                                                       name="show_in_releases_ar" 
                                                       value="1" 
                                                       {{ old('show_in_releases_ar', $post->show_in_releases_ar) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_releases_ar">
                                                    <i class="fas fa-rocket me-1"></i>
                                                    عرض في الإصدارات
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch" style="padding-left: 20.5em;">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_other_reports_ar" 
                                                       name="show_in_other_reports_ar" 
                                                       value="1" 
                                                       {{ old('show_in_other_reports_ar', $post->show_in_other_reports_ar) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_other_reports_ar">
                                                    <i class="fas fa-file-alt me-1"></i>
                                                    عرض في التقارير الأخرى
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- English Display Options -->
                                <div class="tab-pane fade" id="display-en" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch" style="padding-left: 20.5em;">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_slider_en" 
                                                       name="show_in_slider_en" 
                                                       value="1" 
                                                       {{ old('show_in_slider_en', $post->show_in_slider_en) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_slider_en">
                                                    <i class="fas fa-images me-1"></i>
                                                    Show in Slider
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch" style="padding-left: 20.5em;">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_releases_en" 
                                                       name="show_in_releases_en" 
                                                       value="1" 
                                                       {{ old('show_in_releases_en', $post->show_in_releases_en) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_releases_en">
                                                    <i class="fas fa-rocket me-1"></i>
                                                    Show in Releases
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch" style="padding-left: 18.5em;">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_other_reports_en" 
                                                       name="show_in_other_reports_en" 
                                                       value="1" 
                                                       {{ old('show_in_other_reports_en', $post->show_in_other_reports_en) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_other_reports_en">
                                                    <i class="fas fa-file-alt me-1"></i>
                                                    Show in Other Reports
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Settings -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sort_order" class="form-label">
                                <i class="fas fa-sort-numeric-down me-1"></i>
                                ترتيب العرض
                            </label>
                            <input type="number" 
                                   class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', $post->sort_order) }}" 
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
                        
                        <div class="col-md-4 mb-3">
                            <label for="published_at" class="form-label">
                                <i class="fas fa-calendar me-1"></i>
                                تاريخ النشر
                            </label>
                            <input type="datetime-local" 
                                   class="form-control @error('published_at') is-invalid @enderror" 
                                   id="published_at" 
                                   name="published_at" 
                                   value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-eye me-1"></i>
                                المشاهدات
                            </label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-info fs-6">{{ $post->views_count }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Post Statistics -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-chart-bar me-2"></i>
                            إحصائيات الموضوع
                        </h6>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>المشاهدات:</strong> {{ $post->views_count }}
                            </div>
                            <div class="col-md-3">
                                <strong>تاريخ الإنشاء:</strong> {{ $post->created_at->format('Y-m-d H:i') }}
                            </div>
                            <div class="col-md-3">
                                <strong>آخر تحديث:</strong> {{ $post->updated_at->format('Y-m-d H:i') }}
                            </div>
                            <div class="col-md-3">
                                <strong>الحالة:</strong> 
                                @if($post->status == 'published')
                                    <span class="badge bg-success">منشور</span>
                                @elseif($post->status == 'under_review')
                                    <span class="badge bg-warning">قيد المراجعة</span>
                                @else
                                    <span class="badge bg-secondary">مسودة</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        
                        <div>
                            <a href="{{ ($post->category && $post->category->name) ? route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id]) : route('posts.index') }}" class="btn btn-outline-info me-2" target="_blank" rel="noopener">
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
<!-- TinyMCE -->
<script src="{{ asset('dashboard/tinymce/tinymce.min.js') }}"></script>
<script>
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if TinyMCE is loaded
    if (typeof tinymce === 'undefined') {
        console.error('TinyMCE is not loaded');
        return;
    }
    
    // Initialize TinyMCE for Arabic content
    tinymce.init({
        selector: '#content_ar',
        height: 400,
        directionality: 'rtl',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons',
            'codesample', 'nonbreaking', 'pagebreak', 'save', 'directionality'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | ' +
                'alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | ' +
                'forecolor backcolor removeformat | pagebreak | charmap emoticons | ' +
                'fullscreen preview save print | insertfile image media template link anchor codesample | ' +
                'ltr rtl | help',
        menubar: 'file edit view insert format tools table help',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; direction: rtl; }',
        font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
        font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; AkrutiKndPadmini=Akpdmi-n; Times New Roman=times new roman,times,serif; Tahoma=tahoma,arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif',
        image_advtab: true,
        link_assume_external_targets: true,
        file_picker_types: 'image',
        automatic_uploads: true,
        images_upload_url: '/upload',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true
    });

    // Initialize TinyMCE for English content
    tinymce.init({
        selector: '#content_en',
        height: 400,
        directionality: 'ltr',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons',
            'codesample', 'nonbreaking', 'pagebreak', 'save', 'directionality'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | ' +
                'alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | ' +
                'forecolor backcolor removeformat | pagebreak | charmap emoticons | ' +
                'fullscreen preview save print | insertfile image media template link anchor codesample | ' +
                'ltr rtl | help',
        menubar: 'file edit view insert format tools table help',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; direction: ltr; }',
        font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
        font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; AkrutiKndPadmini=Akpdmi-n; Times New Roman=times new roman,times,serif; Tahoma=tahoma,arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif',
        image_advtab: true,
        link_assume_external_targets: true,
        file_picker_types: 'image',
        automatic_uploads: true,
        images_upload_url: '/upload',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true
    });

    // Auto-generate slug from English title (only if current slug matches the pattern)
    document.getElementById('title_en').addEventListener('input', function() {
        const titleEn = this.value;
        const slugField = document.getElementById('slug_ar');
        const originalSlug = '{{ $post->slug }}';
        
        // Only auto-update if the current slug seems to be auto-generated
        if (titleEn && slugField.value === originalSlug) {
            const slug = titleEn
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            
            slugField.value = slug;
        }
    });

    // Multiple files management
    let fileIndexAr = 1;
    let fileIndexEn = 1;

    function addFileInput(lang) {
        const container = document.getElementById(`files-container-${lang}`);
        const currentIndex = lang === 'ar' ? fileIndexAr : fileIndexEn;
        const newFileGroup = document.createElement('div');
        newFileGroup.className = 'file-input-group mb-3';
        newFileGroup.setAttribute('data-index', currentIndex);
        newFileGroup.setAttribute('data-lang', lang);
        
        const langLabel = lang === 'ar' ? '(عربي)' : '(إنجليزي)';
        const placeholder = lang === 'ar' ? 'اسم الملف كما سيظهر للزوار' : 'File name as it will appear to visitors';
        
        newFileGroup.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">
                        <i class="fas fa-paperclip me-1"></i>
                        الملف ${langLabel}
                    </label>
                    <input type="file" 
                           class="form-control" 
                           name="files_${lang}[${currentIndex}][file]" 
                           onchange="autoFillFileName(this, ${currentIndex}, '${lang}')">
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        الحد الأقصى: 10MB، جميع أنواع الملفات مدعومة
                    </div>
                </div>
                <div class="col-md-5 mb-2">
                    <label class="form-label">
                        <i class="fas fa-tag me-1"></i>
                        اسم الملف للعرض ${langLabel}
                    </label>
                    <input type="text" 
                           class="form-control" 
                           name="files_${lang}[${currentIndex}][display_name]" 
                           placeholder="${placeholder}">
                </div>
                <div class="col-md-1 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFileInput(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(newFileGroup);
        
        if (lang === 'ar') {
            fileIndexAr++;
        } else {
            fileIndexEn++;
        }
        
        updateRemoveButtons(lang);
    }

    function removeFileInput(button) {
        const fileGroup = button.closest('.file-input-group');
        const lang = fileGroup.getAttribute('data-lang');
        fileGroup.remove();
        updateRemoveButtons(lang);
    }

    function updateRemoveButtons(lang) {
        const fileGroups = document.querySelectorAll(`#files-container-${lang} .file-input-group`);
        const removeButtons = document.querySelectorAll(`#files-container-${lang} .file-input-group .btn-outline-danger`);
        
        removeButtons.forEach((button, index) => {
            if (fileGroups.length > 1) {
                button.style.display = 'block';
            } else {
                button.style.display = 'none';
            }
        });
    }

    function autoFillFileName(input, index, lang) {
        const file = input.files[0];
        const displayNameField = document.querySelector(`input[name="files_${lang}[${index}][display_name]"]`);
        
        if (file && !displayNameField.value) {
            displayNameField.value = file.name;
        }
    }

    // Image preview function
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const img = preview.querySelector('img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.style.display = 'block';
            };
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }

    // Initialize remove buttons visibility on page load
    updateRemoveButtons('ar');
    updateRemoveButtons('en');

    // Handle form submission to save TinyMCE content
    document.querySelector('form').addEventListener('submit', function(e) {
        // Trigger TinyMCE to save content to textareas
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
    });

    // Make functions global
    window.addFileInput = addFileInput;
    window.removeFileInput = removeFileInput;
    window.autoFillFileName = autoFillFileName;
    window.previewImage = previewImage;
});
</script>
@endsection