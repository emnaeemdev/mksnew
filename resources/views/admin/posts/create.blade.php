@extends('admin.layout')

@section('title', 'إضافة موضوع جديد - MK Snow')
@section('page-title', 'إضافة موضوع جديد')



@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    إضافة موضوع جديد
                </h5>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    العودة للقائمة
                </a>
            </div>
            
            <div class="card-body">
                <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                المعلومات الأساسية
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">
                                        <i class="fas fa-folder me-1"></i>
                                        التصنيف <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" 
                                            id="category_id" 
                                            name="category_id" 
                                            required>
                                        <option value="">اختر التصنيف</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name_ar }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>
                                        الحالة <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Language Tabs -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-language me-2"></i>
                                محتوى المقال
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Language Navigation Tabs -->
                            <ul class="nav nav-tabs mb-4" id="languageTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="arabic-tab" data-bs-toggle="tab" data-bs-target="#arabic-content" type="button" role="tab" aria-controls="arabic-content" aria-selected="true">
                                        <i class="fas fa-flag me-1"></i>
                                        العربية <span class="text-danger">*</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="english-tab" data-bs-toggle="tab" data-bs-target="#english-content" type="button" role="tab" aria-controls="english-content" aria-selected="false">
                                        <i class="fas fa-flag me-1"></i>
                                        English (اختياري)
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="languageTabsContent">
                                <!-- Arabic Content Tab -->
                                <div class="tab-pane fade show active" id="arabic-content" role="tabpanel" aria-labelledby="arabic-tab">
                                    <div class="row">
                                        <!-- Arabic Title -->
                                        <div class="col-md-6 mb-3">
                                            <label for="title_ar" class="form-label">
                                                <i class="fas fa-heading me-1"></i>
                                                العنوان <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('title_ar') is-invalid @enderror" 
                                                   id="title_ar" 
                                                   name="title_ar" 
                                                   value="{{ old('title_ar') }}" 
                                                   placeholder="أدخل عنوان الموضوع بالعربية"
                                                   required>
                                            @error('title_ar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Arabic Slug -->
                                        <div class="col-md-6 mb-3">
                                            <label for="slug" class="form-label">
                                                <i class="fas fa-link me-1"></i>
                                                الرابط المختصر
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('slug') is-invalid @enderror" 
                                                   id="slug" 
                                                   name="slug" 
                                                   value="{{ old('slug') }}" 
                                                   placeholder="سيتم إنشاؤه تلقائياً من العنوان">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                سيتم إنشاؤه تلقائياً إذا تُرك فارغاً
                                            </div>
                                            @error('slug')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Arabic Content -->
                                    <div class="row">
                                        <div class="col-md-12 mb-4">
                                            <label for="content_ar" class="form-label">
                                                <i class="fas fa-align-left me-1"></i>
                                                المحتوى <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control tinymce-editor @error('content_ar') is-invalid @enderror" 
                                                      id="content_ar" 
                                                      name="content_ar" 
                                                      rows="15" 
                                                      placeholder="أدخل محتوى الموضوع بالعربية">{{ old('content_ar') }}</textarea>
                                            @error('content_ar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- English Content Tab -->
                                <div class="tab-pane fade" id="english-content" role="tabpanel" aria-labelledby="english-tab">
                                    <div class="row">
                                        <!-- English Title -->
                                        <div class="col-md-6 mb-3">
                                            <label for="title_en" class="form-label">
                                                <i class="fas fa-heading me-1"></i>
                                                العنوان بالإنجليزية <span class="text-muted">(اختياري)</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('title_en') is-invalid @enderror" 
                                                   id="title_en" 
                                                   name="title_en" 
                                                   value="{{ old('title_en') }}" 
                                                   placeholder="Enter topic title in English (optional)">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                إذا لم يتم إدخال عنوان إنجليزي، سيتم استخدام العنوان العربي في النسخة الإنجليزية
                                            </div>
                                            @error('title_en')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- English Content -->
                                    <div class="row">
                                        <div class="col-md-12 mb-4">
                                            <label for="content_en" class="form-label">
                                                <i class="fas fa-align-left me-1"></i>
                                                المحتوى بالإنجليزية <span class="text-muted">(اختياري)</span>
                                            </label>
                                            <textarea class="form-control tinymce-editor @error('content_en') is-invalid @enderror" 
                                                      id="content_en" 
                                                      name="content_en" 
                                                      rows="15" 
                                                      placeholder="Enter topic content in English (optional)">{{ old('content_en') }}</textarea>
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
                            <!-- Image Language Tabs -->
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
                            
                            <!-- Image Tab Content -->
                            <div class="tab-content" id="imageTabsContent">
                                <!-- Arabic Images Tab -->
                                <div class="tab-pane fade show active" id="arabic-images" role="tabpanel" aria-labelledby="arabic-images-tab">
                                    <div class="row">
                                        <!-- Background Image Arabic -->
                                        <div class="col-md-6 mb-3">
                                            <label for="background_image_ar" class="form-label">
                                                <i class="fas fa-image me-1"></i>
                                                صورة الخلفية
                                            </label>
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
                                        
                                        <!-- Featured Image Arabic -->
                                        <div class="col-md-6 mb-3">
                                            <label for="featured_image_ar" class="form-label">
                                                <i class="fas fa-star me-1"></i>
                                                الصورة المميزة
                                            </label>
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
                                    </div>
                                </div>
                                
                                <!-- English Images Tab -->
                                <div class="tab-pane fade" id="english-images" role="tabpanel" aria-labelledby="english-images-tab">
                                    <div class="row">
                                        <!-- Background Image English -->
                                        <div class="col-md-6 mb-3">
                                            <label for="background_image_en" class="form-label">
                                                <i class="fas fa-image me-1"></i>
                                                صورة الخلفية (إنجليزي)
                                            </label>
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
                                        
                                        <!-- Featured Image English -->
                                        <div class="col-md-6 mb-3">
                                            <label for="featured_image_en" class="form-label">
                                                <i class="fas fa-star me-1"></i>
                                                الصورة المميزة (إنجليزي)
                                            </label>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- File Upload Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-file me-2"></i>
                                الملفات المرفقة
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Files Language Tabs -->
                            <ul class="nav nav-tabs mb-4" id="filesTabs" role="tablist">
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
                            
                            <!-- Files Tab Content -->
                            <div class="tab-content" id="filesTabsContent">
                                <!-- Arabic Files Tab -->
                                <div class="tab-pane fade show active" id="arabic-files" role="tabpanel" aria-labelledby="arabic-files-tab">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">إضافة ملفات للنسخة العربية</span>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFileInput('ar')">
                                            <i class="fas fa-plus me-1"></i>
                                            إضافة ملف عربي
                                        </button>
                                    </div>
                                    <div id="files-container-ar">
                                        <div class="file-input-group mb-3" data-index="0" data-lang="ar">
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">
                                                        <i class="fas fa-paperclip me-1"></i>
                                                        الملف (عربي)
                                                    </label>
                                                    <input type="file" 
                                                           class="form-control" 
                                                           name="files_ar[0][file]" 
                                                           onchange="autoFillFileName(this, 0, 'ar')">
                                                </div>
                                                <div class="col-md-5 mb-2">
                                                    <label class="form-label">
                                                        <i class="fas fa-tag me-1"></i>
                                                        اسم الملف للعرض (عربي)
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="files_ar[0][display_name]" 
                                                           placeholder="اسم الملف كما سيظهر للزوار">
                                                </div>
                                                <div class="col-md-1 mb-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFileInput(this)" style="display: none;">
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
                                        <span class="text-muted">إضافة ملفات للنسخة الإنجليزية</span>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addFileInput('en')">
                                            <i class="fas fa-plus me-1"></i>
                                            إضافة ملف إنجليزي
                                        </button>
                                    </div>
                                    <div id="files-container-en">
                                        <div class="file-input-group mb-3" data-index="0" data-lang="en">
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">
                                                        <i class="fas fa-paperclip me-1"></i>
                                                        الملف (إنجليزي)
                                                    </label>
                                                    <input type="file" 
                                                           class="form-control" 
                                                           name="files_en[0][file]" 
                                                           onchange="autoFillFileName(this, 0, 'en')">
                                                </div>
                                                <div class="col-md-5 mb-2">
                                                    <label class="form-label">
                                                        <i class="fas fa-tag me-1"></i>
                                                        اسم الملف للعرض (إنجليزي)
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="files_en[0][display_name]" 
                                                           placeholder="File name as it will appear to visitors">
                                                </div>
                                                <div class="col-md-1 mb-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFileInput(this)" style="display: none;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                الحد الأقصى لكل ملف: 10MB، جميع أنواع الملفات مدعومة
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
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_slider_ar" 
                                                       name="show_in_slider_ar" 
                                                       value="1" 
                                                       {{ old('show_in_slider_ar') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_slider_ar">
                                                    <i class="fas fa-images me-1"></i>
                                                    عرض في السلايدر
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_releases_ar" 
                                                       name="show_in_releases_ar" 
                                                       value="1" 
                                                       {{ old('show_in_releases_ar') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_releases_ar">
                                                    <i class="fas fa-rocket me-1"></i>
                                                    عرض في الإصدارات
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_other_reports_ar" 
                                                       name="show_in_other_reports_ar" 
                                                       value="1" 
                                                       {{ old('show_in_other_reports_ar') ? 'checked' : '' }}>
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
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_slider_en" 
                                                       name="show_in_slider_en" 
                                                       value="1" 
                                                       {{ old('show_in_slider_en') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_slider_en">
                                                    <i class="fas fa-images me-1"></i>
                                                    Show in Slider
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_releases_en" 
                                                       name="show_in_releases_en" 
                                                       value="1" 
                                                       {{ old('show_in_releases_en') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_releases_en">
                                                    <i class="fas fa-rocket me-1"></i>
                                                    Show in Releases
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="show_in_other_reports_en" 
                                                       name="show_in_other_reports_en" 
                                                       value="1" 
                                                       {{ old('show_in_other_reports_en') ? 'checked' : '' }}>
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
                        
                        <div class="col-md-6 mb-3">
                            <label for="published_at" class="form-label">
                                <i class="fas fa-calendar me-1"></i>
                                تاريخ النشر (اختياري)
                            </label>
                            <input type="datetime-local" 
                                   class="form-control @error('published_at') is-invalid @enderror" 
                                   id="published_at" 
                                   name="published_at" 
                                   value="{{ old('published_at') ? old('published_at') : now()->format('Y-m-d\TH:i') }}">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                إذا تُرك فارغاً، سيتم استخدام التاريخ الحالي عند النشر
                            </div>
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            حفظ الموضوع
                        </button>
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
});

// Auto-generate slug from English title
document.getElementById('title_en').addEventListener('input', function() {
    const titleEn = this.value;
    const slugField = document.getElementById('slug');
    
    if (titleEn && !slugField.value) {
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
    
    // Show remove buttons for all file groups if there's more than one
    updateRemoveButtons(lang);
}

function removeFileInput(button) {
    const fileGroup = button.closest('.file-input-group');
    const lang = fileGroup.getAttribute('data-lang');
    fileGroup.remove();
    
    // Update remove buttons visibility
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

// Handle form submission to ensure TinyMCE content is saved
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submission started');
            
            // Trigger TinyMCE to save content to textareas
            if (typeof tinymce !== 'undefined') {
                console.log('TinyMCE found, triggering save');
                tinymce.triggerSave();
                
                // Wait a moment for TinyMCE to save content
                setTimeout(() => {
                    // Check required fields after TinyMCE save
                    const titleAr = document.getElementById('title_ar').value.trim();
                    const contentAr = document.getElementById('content_ar').value.trim();
                    const categoryId = document.getElementById('category_id').value;
                    
                    if (!titleAr) {
                        e.preventDefault();
                        alert('يرجى إدخال العنوان العربي');
                        return false;
                    }
                    
                    if (!contentAr) {
                        e.preventDefault();
                        alert('يرجى إدخال المحتوى العربي');
                        return false;
                    }
                    
                    if (!categoryId) {
                        e.preventDefault();
                        alert('يرجى اختيار القسم');
                        return false;
                    }
                    // If all validations pass, submit the form
                    if (!e.defaultPrevented) {
                        form.submit();
                    }
                }, 100);
                
                // Prevent immediate submission to allow validation
                e.preventDefault();
            } else {
                console.error('TinyMCE not found');
                
                // Fallback validation if TinyMCE is not available
                const titleAr = document.getElementById('title_ar').value.trim();
                const contentAr = document.getElementById('content_ar').value.trim();
                const categoryId = document.getElementById('category_id').value;
                
                if (!titleAr) {
                    e.preventDefault();
                    alert('يرجى إدخال العنوان العربي');
                    return false;
                }
                
                if (!contentAr) {
                    e.preventDefault();
                    alert('يرجى إدخال المحتوى العربي');
                    return false;
                }
                
                if (!categoryId) {
                    e.preventDefault();
                    alert('يرجى اختيار القسم');
                    return false;
                }
            }
            
            console.log('Form validation passed, submitting...');
        });
    }
});
</script>
@endsection