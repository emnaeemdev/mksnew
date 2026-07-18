@extends('admin.layouts.app')

@section('title', 'إدارة البودكاست')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-podcast me-2"></i>
            قائمة البودكاست
        </h5>
        <a href="{{ route('admin.podcasts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            إضافة حلقة
        </a>
    </div>

    <div class="card-body border-bottom">
        <form method="get" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">البحث</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="ابحث بعنوان أو محتوى">
            </div>
            <div class="col-md-3">
                <label for="published" class="form-label">الحالة</label>
                <select class="form-select" id="published" name="published">
                    <option value="">كل الحالات</option>
                    <option value="1" @selected(request('published')==='1')>منشور</option>
                    <option value="0" @selected(request('published')==='0')>غير منشور</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>
                        تصفية
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>العنوان</th>
                        <th>الترتيب</th>
                        <th>تاريخ النشر</th>
                        <th>منشور؟</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($podcasts as $podcast)
                        <tr>
                            <td>{{ $podcast->id }}</td>
                            <td>
                                <div class="admin-list-item">
                                        @if($podcast->cover_image_url)
                                            <a href="{{ route('admin.podcasts.edit', $podcast) }}" class="admin-list-item__thumb-link">
                                                <img src="{{ $podcast->cover_image_url }}"
                                                     alt=""
                                                     class="admin-list-item__thumb">
                                            </a>
                                        @else
                                            <span class="admin-list-item__thumb-placeholder" aria-hidden="true">&mdash;</span>
                                        @endif
                                        <div class="admin-list-item__body">
                                <a href="{{ route('admin.podcasts.edit', $podcast) }}" class="text-decoration-none text-dark">
                                    <strong>{{ $podcast->title }}</strong>
                                </a>
                                <div class="small text-muted">{{ $podcast->tracks_count ?? $podcast->tracks()->count() }} حلقة</div>
                                        </div>
                                    </div>
                            </td>
                            <td>{{ $podcast->sort_order }}</td>
                            <td>{{ optional($podcast->published_at)->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge bg-{{ $podcast->is_published ? 'success' : 'secondary' }}">{{ $podcast->is_published ? 'منشور' : 'مسودة' }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @if($podcast->is_published)
                                        <a href="{{ route('frontend.podcasts.show', [app()->getLocale(), $podcast->id]) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="btn btn-outline-info"
                                           title="عرض في الموقع">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.podcasts.edit', $podcast) }}"
                                       class="btn btn-outline-warning"
                                       title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.podcasts.destroy', $podcast) }}"
                                          method="post"
                                          class="d-inline"
                                          onsubmit="return confirm('تأكيد الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">لا توجد عناصر</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($podcasts->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $podcasts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
