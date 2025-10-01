@extends('admin.layouts.app')

@section('title', 'أقسام الوثائق')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">أقسام الوثائق</h3>
                    <a href="{{ route('admin.document-sections.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة قسم جديد
                    </a>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($sections->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>اسم القسم</th>
                                        <th>الوصف</th>
                                        <th width="100">عدد الوثائق</th>
                                        <th width="100">عدد الحقول</th>
                                        <th width="80">الترتيب</th>
                                        <th width="80">الحالة</th>
                                        <th width="150">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-sections">
                                    @foreach($sections as $section)
                                        <tr data-id="{{ $section->id }}">
                                            <td>
                                                <i class="fas fa-grip-vertical text-muted" style="cursor: move;"></i>
                                                {{ $section->id }}
                                            </td>
                                            <td>
                                                <strong>{{ $section->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $section->slug }}</small>
                                            </td>
                                            <td>
                                                {{ Str::limit($section->description, 100) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $section->documents_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $section->custom_fields_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $section->sort_order }}</span>
                                            </td>
                                            <td>
                                                @if($section->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.document-sections.show', $section) }}" 
                                                       class="btn btn-sm btn-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.document-sections.edit', $section) }}" 
                                                       class="btn btn-sm btn-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('admin.document-sections.custom-fields.index', $section) }}" 
                                                       class="btn btn-sm btn-secondary" title="الحقول المخصصة">
                                                        <i class="fas fa-cogs"></i>
                                                    </a>
                                                    <form action="{{ route('admin.document-sections.toggle-status', $section) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm {{ $section->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                                title="{{ $section->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}">
                                                            <i class="fas {{ $section->is_active ? 'fa-times' : 'fa-check' }}"></i>
                                                        </button>
                                                    </form>
                                                    @if($section->documents_count == 0)
                                                        <form action="{{ route('admin.document-sections.destroy', $section) }}" 
                                                              method="POST" class="d-inline" 
                                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا القسم؟')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            {{ $sections->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد أقسام حتى الآن</h5>
                            <p class="text-muted">ابدأ بإضافة قسم جديد لتنظيم الوثائق</p>
                            <a href="{{ route('admin.document-sections.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة قسم جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    // تفعيل إعادة الترتيب بالسحب والإفلات
    const sortable = new Sortable(document.getElementById('sortable-sections'), {
        handle: '.fa-grip-vertical',
        animation: 150,
        onEnd: function(evt) {
            const sections = [];
            $('#sortable-sections tr').each(function(index) {
                sections.push({
                    id: $(this).data('id'),
                    sort_order: index + 1
                });
            });
            
            // إرسال الترتيب الجديد للخادم
            $.ajax({
                url: '{{ route("admin.document-sections.reorder") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    sections: sections
                },
                success: function(response) {
                    if (response.success) {
                        // تحديث أرقام الترتيب في الجدول
                        $('#sortable-sections tr').each(function(index) {
                            $(this).find('.badge.bg-light').text(index + 1);
                        });
                        
                        // إظهار رسالة نجاح
                        $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                          response.message +
                          '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                          '</div>').prependTo('.card-body').delay(3000).fadeOut();
                    }
                },
                error: function() {
                    alert('حدث خطأ أثناء إعادة الترتيب');
                    location.reload();
                }
            });
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.sortable-ghost {
    opacity: 0.4;
}

.fa-grip-vertical {
    cursor: move !important;
}

#sortable-sections tr:hover {
    background-color: #f8f9fa;
}
</style>
@endpush