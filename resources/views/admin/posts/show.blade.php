@extends('admin.layout')

@section('title', 'عرض الموضوع: ' . $post->title . ' - MK Snow')
@section('page-title', 'عرض الموضوع: ' . Str::limit($post->title, 50))



@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-eye me-2"></i>
                    {{ Str::limit($post->title, 60) }}
                </h5>
                <div>
                    <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-outline-warning me-2">
                        <i class="fas fa-edit me-2"></i>
                        تعديل
                    </a>
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Post Status and Info -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-2">حالة الموضوع</h6>
                            @if($post->status == 'published')
                                <span class="badge bg-success status-badge">
                                    <i class="fas fa-check-circle me-1"></i>
                                    منشور
                                </span>
                            @elseif($post->status == 'under_review')
                                <span class="badge bg-warning status-badge">
                                    <i class="fas fa-clock me-1"></i>
                                    قيد المراجعة
                                </span>
                            @else
                                <span class="badge bg-secondary status-badge">
                                    <i class="fas fa-file-alt me-1"></i>
                                    مسودة
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-2">القسم</h6>
                            <span class="badge bg-primary status-badge">
                                <i class="fas fa-tag me-1"></i>
                                {{ $post->category->name_ar }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-2">المشاهدات</h6>
                            <span class="badge bg-info status-badge">
                                <i class="fas fa-eye me-1"></i>
                                {{ $post->views_count }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-2">تاريخ النشر</h6>
                            <span class="badge bg-dark status-badge">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $post->published_at ? $post->published_at->format('Y-m-d') : 'غير محدد' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Post Details -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            تفاصيل الموضوع
                        </h6>
                        
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 40%;"><strong>الرابط المختصر:</strong></td>
                                <td><code>{{ $post->slug }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>ترتيب العرض:</strong></td>
                                <td>{{ $post->sort_order }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>تاريخ الإنشاء:</strong></td>
                                <td>{{ $post->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>آخر تحديث:</strong></td>
                                <td>{{ $post->updated_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-cog me-2"></i>
                            خيارات العرض
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">
                                    <i class="fas fa-flag me-1"></i>
                                    خيارات العرض - العربية
                                </h6>
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" {{ $post->show_in_slider_ar ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">
                                            <i class="fas fa-images me-1"></i>
                                            عرض في السلايدر
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" {{ $post->show_in_releases_ar ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">
                                            <i class="fas fa-rocket me-1"></i>
                                            عرض في الإصدارات
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" {{ $post->show_in_other_reports_ar ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">
                                            <i class="fas fa-file-alt me-1"></i>
                                            عرض في التقارير الأخرى
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">
                                    <i class="fas fa-flag me-1"></i>
                                    خيارات العرض - الإنجليزية
                                </h6>
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" {{ $post->show_in_slider_en ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">
                                            <i class="fas fa-images me-1"></i>
                                            عرض في السلايدر
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" {{ $post->show_in_releases_en ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">
                                            <i class="fas fa-rocket me-1"></i>
                                            عرض في الإصدارات
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" {{ $post->show_in_other_reports_en ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">
                                            <i class="fas fa-file-alt me-1"></i>
                                            عرض في التقارير الأخرى
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Post Titles -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-heading me-2"></i>
                            العنوان بالعربية
                        </h6>
                        <div class="alert alert-light">
                            <h5 class="mb-0">{{ $post->title_ar }}</h5>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-heading me-2"></i>
                            العنوان بالإنجليزية
                        </h6>
                        <div class="alert alert-light">
                            <h5 class="mb-0">{{ $post->title_en }}</h5>
                        </div>
                    </div>
                </div>
                
                <!-- Post Content -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-align-right me-2"></i>
                            المحتوى بالعربية
                        </h6>
                        <div class="post-content">
                            {!! $post->content_ar !!}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-align-left me-2"></i>
                            المحتوى بالإنجليزية
                        </h6>
                        <div class="post-content">
                             {!! $post->content_en !!}
                        </div>
                    </div>
                </div>
                
                <!-- Images -->
                @if($post->background_image || $post->featured_image)
                <div class="row mb-4" style="border: 2px solid #000000;">
                    <div class="col-md-12">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-images me-2"></i>
                            الصور
                        </h6>
                    </div>
                    
                    @if($post->background_image_ar)
                    <div class="col-md-6 mb-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-2">صورة الخلفية</h6>
                            <img src="{{ asset('storage/' . $post->background_image_ar) }}" 
                                 alt="صورة الخلفية" 
                                 class="post-image">
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $post->background_image_ar) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    عرض بالحجم الكامل
                                </a>
                                
                            </div>
                            
                        </div>
                        
                    </div>
                                                          @else
                    لا يوجد صورة خلفية
  
                    @endif
                    
                    @if($post->featured_image_ar)
                    <div class="col-md-6 mb-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-2">الصورة المميزة</h6>
                            <img src="{{ asset('storage/' . $post->featured_image_ar) }}" 
                                 alt="الصورة المميزة" 
                                 class="post-image">
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $post->featured_image_ar) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    عرض بالحجم الكامل
                                </a>
                            </div>
                        </div>
                    </div>
                                                          @else
                    لا يوجد صورة مميزة
                    @endif
                    
                </div>
                @endif


                @if($post->background_image_en || $post->featured_image_en)
                <div class="row mb-4" style="border: 2px solid #000000;">
                    <div class="col-md-12">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-images me-2"></i>
                          بالانجليزية  الصور
                        </h6>
                    </div>
                    
                    @if($post->background_image_en)
                    <div class="col-md-6 mb-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-2">صورة الخلفية</h6>
                            <img src="{{ asset('storage/' . $post->background_image_en) }}" 
                                 alt="صورة الخلفية" 
                                 class="post-image">
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $post->background_image_en) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    عرض بالحجم الكامل
                                </a>
                            </div>
                        </div>
                    </div>
                    @else
                    لا يوجد صورة خلفية
                    @endif
                    
                    @if($post->featured_image_en)
                    <div class="col-md-6 mb-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-2">الصورة المميزة</h6>
                            <img src="{{ asset('storage/' . $post->featured_image_en) }}" 
                                 alt="الصورة المميزة" 
                                 class="post-image">
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $post->featured_image_en) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    عرض بالحجم الكامل
                                </a>
                            </div>
                        </div>
                    </div>
                                                          @else
                    لا يوجد صورة مميزة
                    @endif
                    
                </div>
                @endif
                
                <!-- File Attachments -->
                @if($post->files->count() > 0)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-file me-2"></i>
                            الملفات المرفقة ({{ $post->files->count() }})
                        </h6>
                        
                        <div class="row">
                            @foreach($post->files as $file)
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-file me-2"></i>
                                                    {{ $file->display_name }}
                                                </h6>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    الاسم الأصلي: {{ $file->original_name }}
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-hdd me-1"></i>
                                                    الحجم: {{ $file->formattedSize }}
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-file-code me-1"></i>
                                                    النوع: {{ $file->mime_type }}
                                                </small>
                                            </div>
                                            <div class="ms-3">
                                                <a href="{{ $file->fileUrl }}" 
                                                   class="btn btn-primary btn-sm"
                                                   download="{{ $file->display_name }}">
                                                    <i class="fas fa-download me-1"></i>
                                                    تحميل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-bolt me-2"></i>
                                    إجراءات سريعة
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-warning w-100">
                                            <i class="fas fa-edit me-2"></i>
                                            تعديل الموضوع
                                        </a>
                                    </div>
                                    
                                    <div class="col-md-3 mb-2">
                                        <a href="{{ route('admin.posts.create') }}" class="btn btn-success w-100">
                                            <i class="fas fa-plus me-2"></i>
                                            إضافة موضوع جديد
                                        </a>
                                    </div>
                                    
                                    <div class="col-md-3 mb-2">
                                        <a href="{{ route('admin.categories.show', $post->category) }}" class="btn btn-info w-100">
                                            <i class="fas fa-tag me-2"></i>
                                            عرض القسم
                                        </a>
                                    </div>
                                    
                                    <div class="col-md-3 mb-2">
                                        <form action="{{ route('admin.posts.destroy', $post) }}" 
                                              method="POST" 
                                              class="d-inline w-100"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا الموضوع؟ سيتم حذف جميع الملفات المرتبطة به.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="fas fa-trash me-2"></i>
                                                حذف الموضوع
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-refresh views count every 30 seconds
setInterval(function() {
    fetch('{{ route('admin.posts.show', [$post]) }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Extract views count from response and update
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newViewsElement = doc.querySelector('.badge.bg-info');
        if (newViewsElement) {
            const currentViewsElement = document.querySelector('.badge.bg-info');
            if (currentViewsElement) {
                currentViewsElement.innerHTML = newViewsElement.innerHTML;
            }
        }
    })
    .catch(error => console.log('Error updating views:', error));
}, 30000);
</script>
@endsection