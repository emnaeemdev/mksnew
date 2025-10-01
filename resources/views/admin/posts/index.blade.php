@extends('admin.layout')

@section('title', 'إدارة المواضيع - MK Snow')
@section('page-title', 'إدارة المواضيع')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-alt me-2"></i>
            قائمة المواضيع
        </h5>
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            إضافة موضوع جديد
        </a>
    </div>
    
    <!-- Filters -->
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('admin.posts.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="category" class="form-label">تصفية حسب القسم</label>
                <select name="category" id="category" class="form-select">
                    <option value="">جميع الأقسام</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">تصفية حسب الحالة</label>
                <select name="status" id="status" class="form-select">
                    <option value="">جميع الحالات</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منشور</option>
                    <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>قيد المراجعة</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="search" class="form-label">البحث</label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="{{ request('search') }}" placeholder="البحث في العناوين...">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>
                        بحث
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="card-body">
        @if($posts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>القسم</th>
                            <th>الحالة</th>
                            <th>اللغات</th>
                            <th>المشاهدات</th>
                            <th>الصور</th>
                            <th>الملفات</th>
                            <th>تاريخ النشر</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                            <tr>
                                <td>{{ $post->id }}</td>
                                <td>
                                    <div>
                                        <strong>
                                            
                                                {{ Str::limit($post->title_ar, 40) }}



                                        </strong>
                                        @if($post->show_in_slider_ar)
                                            <span class="badge bg-info ms-1" title="يظهر في السلايدر - عربي">
                                                <i class="fas fa-images"></i> ع
                                            </span>
                                        @endif

                                        @if($post->show_in_releases_ar)
                                            <span class="badge bg-success ms-1" title="يظهر في الإصدارات - عربي">
                                                <i class="fas fa-rocket"></i> ع
                                            </span>
                                        @endif

                                        @if($post->show_in_other_reports_ar)
                                            <span class="badge bg-warning ms-1" title="يظهر في التقارير الأخرى - عربي">
                                                <i class="fas fa-file-alt"></i> ع
                                            </span>
                                        @endif
  
                                    </div>
                                    @if($post->title_en)
                                    <div>
                                    <span class="badge bg-success"> English </span>
                                      {{ Str::limit($post->title_en, 40) }}
                                      @endif
                                      @if($post->show_in_slider_en)
                                            <span class="badge bg-info ms-1" title="يظهر في السلايدر - إنجليزي">
                                                <i class="fas fa-images"></i> E
                                            </span>
                                        @endif
                                        @if($post->show_in_releases_en)
                                            <span class="badge bg-success ms-1" title="يظهر في الإصدارات - إنجليزي">
                                                <i class="fas fa-rocket"></i> E
                                            </span>
                                        @endif
                                         @if($post->show_in_other_reports_en)
                                            <span class="badge bg-warning ms-1" title="يظهر في التقارير الأخرى - إنجليزي">
                                                <i class="fas fa-file-alt"></i> E
                                            </span>
                                        @endif
                                                                                
                                    <!-- <small class="text-muted">{{ Str::limit($post->content_ar, 60) }}</small> -->
                                     <div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $post->category->name_ar }}</span>
                                    
                                </td>
                                <td>
                                    @if($post->status == 'published')
                                        <span class="badge bg-success">منشور</span>
                                    @elseif($post->status == 'under_review')
                                        <span class="badge bg-warning">قيد المراجعة</span>
                                    @else
                                        <span class="badge bg-secondary">مسودة</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <span class="badge bg-success" title="العربية متوفرة">
                                            <i class="fas fa-check"></i> ع
                                        </span>
                                        @if($post->title_en || $post->content_en)
                                            <span class="badge bg-primary" title="الإنجليزية متوفرة">
                                                <i class="fas fa-check"></i> EN
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted" title="الإنجليزية غير متوفرة">
                                                <i class="fas fa-times"></i> EN
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $post->views_count }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($post->background_image_ar || $post->background_image_en)
                                            <span class="badge bg-primary" title="صورة خلفية">
                                                <i class="fas fa-image"></i>
                                            </span>
                                        @endif
                                        @if($post->featured_image_ar || $post->featured_image_en)
                                            <span class="badge bg-success" title="صورة مميزة">
                                                <i class="fas fa-star"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($post->files && $post->files->count() > 0)
                                        <span class="badge bg-info" title="{{ $post->files->count() }} ملفات مرفقة">
                                            <i class="fas fa-file"></i> {{ $post->files->count() }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-muted">
                                    @if($post->published_at)
                                        {{ $post->published_at->format('Y-m-d') }}
                                        <br>
                                        <small>{{ $post->published_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">غير منشور</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.posts.show', $post) }}" 
                                           class="btn btn-outline-info btn-sm" 
                                           title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a href="{{ route('admin.posts.edit', $post) }}" 
                                           class="btn btn-outline-warning btn-sm" 
                                           title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($post->status == 'published')
                                            <a href="{{ route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id]) }}" 
                                               target="_blank"
                                               class="btn btn-outline-success btn-sm" 
                                               title="عرض في الموقع">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                        
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm" 
                                                title="حذف"
                                                onclick="confirmDelete({{ $post->id }}, '{{ addslashes($post->title) }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Hidden Delete Form -->
                                    <form id="delete-form-{{ $post->id }}" 
                                          action="{{ route('admin.posts.destroy', $post) }}" 
                                          method="POST" 
                                          style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($posts->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @endif
            
            <!-- Results Info -->
            <div class="text-center text-muted mt-3">
                عرض {{ $posts->firstItem() }} إلى {{ $posts->lastItem() }} من أصل {{ $posts->total() }} موضوع
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                @if(request()->hasAny(['category', 'status', 'search']))
                    <h4 class="text-muted">لا توجد مواضيع تطابق معايير البحث</h4>
                    <p class="text-muted mb-4">جرب تغيير معايير البحث أو الفلترة</p>
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-times me-2"></i>
                        إزالة الفلاتر
                    </a>
                @else
                    <h4 class="text-muted">لا توجد مواضيع حتى الآن</h4>
                    <p class="text-muted mb-4">ابدأ بإضافة أول موضوع للموقع</p>
                @endif
                <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إضافة موضوع جديد
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(postId, postTitle) {
    if (confirm('هل أنت متأكد من حذف الموضوع "' + postTitle + '"؟\n\nسيتم حذف جميع الملفات والصور المرتبطة بهذا الموضوع أيضاً!')) {
        document.getElementById('delete-form-' + postId).submit();
    }
}

// Auto-submit form on filter change
document.getElementById('category').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('status').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection