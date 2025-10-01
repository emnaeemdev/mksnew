@extends('admin.layout')

@section('title', 'إدارة بيانات: ' . $section->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        @if($section->icon)
                            <i class="{{ $section->icon }}"></i>
                        @endif
                        إدارة بيانات: {{ $section->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.section-data.create', $section) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة بيانات جديدة
                        </a>
                        <a href="{{ route('admin.sections.show', $section) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للقسم
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           value="{{ request('search') }}" placeholder="البحث في البيانات...">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="">جميع الحالات</option>
                                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>منشور</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>مؤرشف</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                @if(request('search') || request('status'))
                                    <a href="{{ route('admin.section-data.index', $section) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> مسح الفلاتر
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($data->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>العنوان</th>
                                        <th>الحالة</th>
                                        <th>المؤلف</th>
                                        <th>تاريخ النشر</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->title }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $item->slug }}</small>
                                            </td>
                                            <td>
                                                @if($item->status === 'published')
                                                    <span class="badge bg-success">منشور</span>
                                                @elseif($item->status === 'draft')
                                                    <span class="badge bg-warning">مسودة</span>
                                                @else
                                                    <span class="badge bg-secondary">مؤرشف</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->user->name ?? 'غير محدد' }}</td>
                                            <td>
                                                @if($item->published_at)
                                                    {{ $item->published_at->format('Y-m-d') }}
                                                @else
                                                    <span class="text-muted">غير منشور</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.section-data.show', [$section, $item]) }}" 
                                                       class="btn btn-sm btn-outline-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.section-data.edit', [$section, $item]) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.section-data.destroy', [$section, $item]) }}" 
                                                          method="POST" class="d-inline" 
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه البيانات؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $data->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            @if(request('search') || request('status'))
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">لا توجد نتائج</h5>
                                <p class="text-muted">لم يتم العثور على بيانات تطابق معايير البحث</p>
                                <a href="{{ route('admin.section-data.index', $section) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> مسح الفلاتر
                                </a>
                            @else
                                <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">لا توجد بيانات</h5>
                                <p class="text-muted">ابدأ بإضافة بيانات جديدة لهذا القسم</p>
                                <a href="{{ route('admin.section-data.create', $section) }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة بيانات جديدة
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
}
.btn-group .btn {
    margin-right: 2px;
}
.badge {
    font-size: 0.75em;
}
.input-group .btn {
    border-color: #ced4da;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit search form on Enter
    $('input[name="search"]').on('keypress', function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });
});
</script>
@endpush