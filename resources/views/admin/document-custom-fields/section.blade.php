@extends('admin.layouts.app')

@section('title', 'حقول القسم: ' . $section->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> حقول القسم: {{ $section->name }}
                    </h3>
                    <div>
                        <a href="{{ route('admin.document-custom-fields.create', ['section' => $section->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة حقل جديد
                        </a>
                        <a href="{{ route('admin.document-sections.show', $section) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للقسم
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($fields->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">الترتيب</th>
                                        <th>اسم الحقل</th>
                                        <th>النوع</th>
                                        <th>مطلوب</th>
                                        <th>الحالة</th>
                                        <th width="200">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-fields">
                                    @foreach($fields as $field)
                                    <tr data-id="{{ $field->id }}">
                                        <td>
                                            <span class="badge bg-secondary sort-handle" style="cursor: move;">
                                                <i class="fas fa-grip-vertical"></i> {{ $field->sort_order }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $field->label }}</strong>
                                                <br><small class="text-muted">{{ $field->name }}</small>
                                                @if($field->help_text)
                                                    <br><small class="text-info"><i class="fas fa-info-circle"></i> {{ Str::limit($field->help_text, 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @switch($field->type)
                                                @case('text')
                                                    <span class="badge bg-primary">نص</span>
                                                    @break
                                                @case('textarea')
                                                    <span class="badge bg-info">نص طويل</span>
                                                    @break
                                                @case('number')
                                                    <span class="badge bg-success">رقم</span>
                                                    @break
                                                @case('date')
                                                    <span class="badge bg-warning">تاريخ</span>
                                                    @break
                                                @case('select')
                                                    <span class="badge bg-secondary">قائمة منسدلة</span>
                                                    @break
                                                @case('multiselect')
                                                    <span class="badge bg-dark">اختيارات متعددة</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light text-dark">{{ $field->type }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($field->is_required)
                                                <span class="badge bg-danger">مطلوب</span>
                                            @else
                                                <span class="badge bg-success">اختياري</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($field->is_active)
                                                <span class="badge bg-success">مفعل</span>
                                            @else
                                                <span class="badge bg-secondary">غير مفعل</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.document-custom-fields.show', [$section, $field]) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.document-custom-fields.edit', [$section, $field]) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.document-custom-fields.destroy', [$section, $field]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحقل؟')">
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
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $fields->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد حقول مخصصة في هذا القسم</h5>
                            <p class="text-muted">ابدأ بإضافة حقل جديد لهذا القسم</p>
                            <a href="{{ route('admin.document-custom-fields.create', ['section' => $section->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة حقل جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // تفعيل السحب والإفلات لإعادة الترتيب
    $('#sortable-fields').sortable({
        handle: '.sort-handle',
        update: function(event, ui) {
            var fields = [];
            $('#sortable-fields tr').each(function(index) {
                fields.push({
                    id: $(this).data('id'),
                    sort_order: index + 1
                });
            });
            
            $.ajax({
                url: '{{ route("admin.document-custom-fields.reorder") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    fields: fields
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        // تحديث أرقام الترتيب في الواجهة
                        $('#sortable-fields tr').each(function(index) {
                            $(this).find('.sort-handle').html('<i class="fas fa-grip-vertical"></i> ' + (index + 1));
                        });
                    }
                },
                error: function() {
                    toastr.error('حدث خطأ أثناء إعادة الترتيب');
                    location.reload();
                }
            });
        }
    });
});
</script>
@endpush
@endsection