@extends('admin.layout')

@section('title', 'لوحة التحكم -mksegypt')
@section('page-title', 'لوحة التحكم')

@section('content')
<div class="row dashboard-stats-row g-2 mb-3">
    <div class="col-md-3 col-6">
        <div class="card dashboard-stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 dashboard-stat-card__row">
                    <div class="dashboard-stat-card__icon text-primary">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="flex-grow-1 dashboard-stat-card__meta">
                        <div class="dashboard-stat-card__value">{{ $stats['categories_count'] ?? 0 }}</div>
                        <div class="dashboard-stat-card__label">إجمالي الأقسام</div>
                    </div>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-primary btn-sm dashboard-stat-card__btn">عرض</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card dashboard-stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 dashboard-stat-card__row">
                    <div class="dashboard-stat-card__icon text-success">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="flex-grow-1 dashboard-stat-card__meta">
                        <div class="dashboard-stat-card__value">{{ $stats['posts_count'] ?? 0 }}</div>
                        <div class="dashboard-stat-card__label">إجمالي المواضيع</div>
                    </div>
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-success btn-sm dashboard-stat-card__btn">عرض</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card dashboard-stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 dashboard-stat-card__row">
                    <div class="dashboard-stat-card__icon text-info">
                        <i class="fas fa-file-text"></i>
                    </div>
                    <div class="flex-grow-1 dashboard-stat-card__meta">
                        <div class="dashboard-stat-card__value">{{ $stats['documents_count'] ?? 0 }}</div>
                        <div class="dashboard-stat-card__label">إجمالي الوثائق</div>
                    </div>
                    <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-info btn-sm dashboard-stat-card__btn">عرض</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card dashboard-stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 dashboard-stat-card__row">
                    <div class="dashboard-stat-card__icon text-warning">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="flex-grow-1 dashboard-stat-card__meta">
                        <div class="dashboard-stat-card__value">{{ $stats['document_sections_count'] ?? 0 }}</div>
                        <div class="dashboard-stat-card__label">أقسام الوثائق</div>
                    </div>
                    <a href="{{ route('admin.document-sections.index') }}" class="btn btn-outline-warning btn-sm dashboard-stat-card__btn">عرض</a>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100 dashboard-recent-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 dashboard-recent-card__title">
                    <i class="fas fa-newspaper me-2"></i>
                    أحدث المواضيع
                    <small class="text-muted fw-normal">— تقارير أوراق أدلة هل تعلم</small>
                </h6>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-primary btn-sm">
                    عرض الكل
                </a>
            </div>
            <div class="card-body">
                @if(isset($recentPosts) && $recentPosts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 dashboard-recent-card__table">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>القسم الفرعي</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPosts as $post)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="text-decoration-none text-dark">
                                                {{ Str::limit($post->title, 45) }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($post->category)
                                                <a href="{{ route('admin.posts.index', ['category' => $post->category_id]) }}"
                                                   class="badge bg-secondary text-decoration-none">
                                                    {{ $post->category->name_ar }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
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
                        <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                        <p class="text-muted small mb-3">لا توجد مواضيع في أقسام «هل تعلم» حتى الآن</p>
                        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>
                            إضافة موضوع جديد
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100 dashboard-recent-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 dashboard-recent-card__title">
                    <i class="fas fa-file-text me-2"></i>
                    أحدث الوثائق
                </h6>
                <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-info btn-sm">
                    عرض الكل
                </a>
            </div>
            <div class="card-body">
                @if(isset($recentDocuments) && $recentDocuments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 dashboard-recent-card__table">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>قسم الوثائق</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDocuments as $document)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.documents.edit', $document) }}" class="text-decoration-none text-dark">
                                                {{ Str::limit($document->title, 45) }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($document->section)
                                                <a href="{{ route('admin.documents.index', ['section_id' => $document->section_id]) }}"
                                                   class="badge bg-info text-decoration-none">
                                                    {{ $document->section->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($document->is_published)
                                                <span class="badge bg-success">منشور</span>
                                            @else
                                                <span class="badge bg-secondary">مسودة</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">
                                            {{ $document->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-text fa-2x text-muted mb-2"></i>
                        <p class="text-muted small mb-3">لا توجد وثائق حتى الآن</p>
                        <a href="{{ route('admin.documents.create') }}" class="btn btn-info btn-sm text-white">
                            <i class="fas fa-plus me-2"></i>
                            إضافة وثيقة جديدة
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
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