@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>إدارة البودكاست</h4>
        <a href="{{ route('admin.podcasts.create') }}" class="btn btn-primary">إضافة حلقة</a>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="ابحث بعنوان أو محتوى">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="published">
                <option value="">كل الحالات</option>
                <option value="1" @selected(request('published')==='1')>منشور</option>
                <option value="0" @selected(request('published')==='0')>غير منشور</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-secondary">تصفية</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العنوان</th>
                    <th>الترتيب</th>
                    <th>تاريخ النشر</th>
                    <th>منشور؟</th>
                    <th>خيارات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($podcasts as $podcast)
                    <tr>
                        <td>{{ $podcast->id }}</td>
                        <td>{{ $podcast->title }}</td>
                        <td>{{ $podcast->sort_order }}</td>
                        <td>{{ optional($podcast->published_at)->format('Y-m-d') }}</td>
                        <td>
                            <span class="badge bg-{{ $podcast->is_published ? 'success' : 'secondary' }}">{{ $podcast->is_published ? 'منشور' : 'مسودة' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.podcasts.edit', $podcast) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <a href="{{ route('admin.podcasts.show', $podcast) }}" class="btn btn-sm btn-info">عرض</a>
                            <form action="{{ route('admin.podcasts.destroy', $podcast) }}" method="post" class="d-inline" onsubmit="return confirm('تأكيد الحذف؟');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">لا توجد عناصر</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $podcasts->links() }}
</div>
@endsection