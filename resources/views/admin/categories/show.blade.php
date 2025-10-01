@extends('admin.layout')

@section('title', 'عرض القسم: ' . $category->name . ' - MK Snow')
@section('page-title', 'عرض القسم: ' . $category->name)

@section('content')
<div class="row">
    <!-- Category Details -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-tag me-2"></i>
                    تفاصيل القسم
                </h5>
                <div>
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>
                        تعديل
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-tag me-1"></i>
                            الاسم بالعربية
                        </h6>
                        <p class="h5 mb-4">{{ $category->name_ar }}</p>
                        
                        @if($category->description_ar)
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-align-left me-1"></i>
                                الوصف بالعربية
                            </h6>
                            <p class="mb-4">{{ $category->description_ar }}</p>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-tag me-1"></i>
                            الاسم بالإنجليزية
                        </h6>
                        <p class="h5 mb-4">{{ $category->name_en }}</p>
                        
                        @if($category->description_en)
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-align-left me-1"></i>
                                الوصف بالإنجليزية
                            </h6>
                            <p class="mb-4">{{ $category->description_en }}</p>
                        @endif
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-link me-1"></i>
                            الرابط المختصر
                        </h6>
                        <p><code>{{ $category->slug }}</code></p>
                    </div>
                    
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-toggle-on me-1"></i>
                            الحالة
                        </h6>
                        <p>
                            @if($category->is_active)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>
                                    نشط
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    غير نشط
                                </span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-sort-numeric-down me-1"></i>
                            ترتيب العرض
                        </h6>
                        <p><span class="badge bg-primary">{{ $category->sort_order }}</span></p>
                    </div>
                    
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-calendar me-1"></i>
                            تاريخ الإنشاء
                        </h6>
                        <p>{{ $category->created_at->format('Y-m-d H:i') }}</p>
                        <small class="text-muted">{{ $category->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Category Posts -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    مواضيع القسم ({{ $category->posts()->count() }})
                </h5>
                <a href="{{ route('admin.posts.create', ['category' => $category->id]) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-2"></i>
                    إضافة موضوع جديد
                </a>
            </div>
            
            <div class="card-body">
                @if($category->posts()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>الحالة</th>
                                    <th>المشاهدات</th>
                                    <th>تاريخ النشر</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->posts()->latest()->take(10)->get() as $post)
                                    <tr>
                                        <td>
                                            <strong>{{ Str::limit($post->title, 60) }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($post->content, 80) }}</small>
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
                                            <span class="badge bg-info">{{ $post->views_count }}</span>
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
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($category->posts()->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.posts.index', ['category' => $category->id]) }}" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>
                                عرض جميع المواضيع ({{ $category->posts()->count() }})
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد مواضيع في هذا القسم</h5>
                        <p class="text-muted mb-4">ابدأ بإضافة أول موضوع لهذا القسم</p>
                        <a href="{{ route('admin.posts.create', ['category' => $category->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            إضافة موضوع جديد
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Statistics Sidebar -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    إحصائيات القسم
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-primary mb-1">{{ $category->posts()->count() }}</h3>
                            <small class="text-muted">إجمالي المواضيع</small>
                        </div>
                    </div>
                    
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success mb-1">{{ $category->posts()->where('status', 'published')->count() }}</h3>
                            <small class="text-muted">المواضيع المنشورة</small>
                        </div>
                    </div>
                    
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-warning mb-1">{{ $category->posts()->where('status', 'under_review')->count() }}</h3>
                            <small class="text-muted">قيد المراجعة</small>
                        </div>
                    </div>
                    
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-secondary mb-1">{{ $category->posts()->where('status', 'draft')->count() }}</h3>
                            <small class="text-muted">المسودات</small>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <h3 class="text-info mb-1">{{ $category->posts()->sum('views_count') }}</h3>
                            <small class="text-muted">إجمالي المشاهدات</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.posts.create', ['category' => $category->id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        إضافة موضوع جديد
                    </a>
                    
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-outline-warning">
                        <i class="fas fa-edit me-2"></i>
                        تعديل القسم
                    </a>
                    
                    <a href="{{ route('admin.posts.index', ['category' => $category->id]) }}" class="btn btn-outline-info">
                        <i class="fas fa-list me-2"></i>
                        عرض جميع المواضيع
                    </a>
                    
                    @if($category->is_active)
                        <a href="{{ route('posts.index', ['category' => $category->name]) }}" target="_blank" class="btn btn-outline-success">
                            <i class="fas fa-external-link-alt me-2"></i>
                            عرض في الموقع
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection