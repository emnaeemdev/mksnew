@extends('admin.layout')

@section('title', 'لوحة التحكم - MK Snow')
@section('page-title', 'لوحة التحكم')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="fas fa-tags fa-2x text-primary"></i>
                    </div>
                </div>
                <h3 class="card-title">{{ $stats['categories_count'] ?? 0 }}</h3>
                <p class="card-text text-muted">إجمالي الأقسام</p>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-primary btn-sm">
                    عرض الأقسام
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fas fa-file-alt fa-2x text-success"></i>
                    </div>
                </div>
                <h3 class="card-title">{{ $stats['posts_count'] ?? 0 }}</h3>
                <p class="card-text text-muted">إجمالي المواضيع</p>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-success btn-sm">
                    عرض المواضيع
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="fas fa-file-text fa-2x text-info"></i>
                    </div>
                </div>
                <h3 class="card-title">{{ $stats['documents_count'] ?? 0 }}</h3>
                <p class="card-text text-muted">إجمالي الوثائق</p>
                <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-info btn-sm">
                    عرض الوثائق
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="fas fa-folder fa-2x text-warning"></i>
                    </div>
                </div>
                <h3 class="card-title">{{ $stats['document_sections_count'] ?? 0 }}</h3>
                <p class="card-text text-muted">أقسام الوثائق</p>
                <a href="{{ route('admin.document-sections.index') }}" class="btn btn-outline-warning btn-sm">
                    عرض الأقسام
                </a>
            </div>
        </div>
    </div>

</div>



<div class="row">
    <!-- Recent Posts -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    أحدث المواضيع
                </h5>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-primary btn-sm">
                    عرض الكل
                </a>
            </div>
            <div class="card-body">
                @if(isset($recentPosts) && $recentPosts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>القسم</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الإنشاء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPosts as $post)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.posts.show', $post) }}" class="text-decoration-none">
                                                {{ Str::limit($post->title, 50) }}
                                            </a>
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
                                        <td class="text-muted">
                                            {{ $post->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد مواضيع حتى الآن</p>
                        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            إضافة موضوع جديد
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        إضافة موضوع جديد
                    </a>
                    
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-tags me-2"></i>
                        إضافة قسم جديد
                    </a>
                    
                    <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-info">
                        <i class="fas fa-external-link-alt me-2"></i>
                        عرض الموقع
                    </a>
                    
                    <hr>
                    
                    {{-- <div class="text-center">
                        <h6 class="text-muted mb-2">تغيير اللغة</h6>
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('language.switch', 'ar') }}" class="btn btn-outline-secondary {{ app()->getLocale() == 'ar' ? 'active' : '' }}">
                                العربية
                            </a>
                            <a href="{{ route('language.switch', 'en') }}" class="btn btn-outline-secondary {{ app()->getLocale() == 'en' ? 'active' : '' }}">
                                English
                            </a>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection