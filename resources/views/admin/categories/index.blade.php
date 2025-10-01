@extends('admin.layout')

@section('title', 'إدارة الأقسام - MK Snow')
@section('page-title', 'إدارة الأقسام')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-tags me-2"></i>
            قائمة الأقسام
        </h5>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            إضافة قسم جديد
        </a>
    </div>
    
    <div class="card-body">
        @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-arrows-alt"></i></th>
                            <th>#</th>
                            <th>الاسم بالعربية</th>
                            <th>الاسم بالإنجليزية</th>
                            <th>الرابط المختصر</th>
                            <th>عدد المواضيع</th>
                            <th>الحالة</th>
                            <th>ترتيب العرض</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-categories">
                        @foreach($categories as $category)
                            <tr data-id="{{ $category->id }}" style="cursor: move;">
                                <td class="drag-handle text-center">
                                    <i class="fas fa-grip-vertical text-muted"></i>
                                </td>
                                <td>{{ $category->id }}</td>
                                <td>
                                    <strong>{{ $category->name_ar }}</strong>
                                    @if($category->description_ar)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($category->description_ar, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $category->name_en }}</strong>
                                    @if($category->description_en)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($category->description_en, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $category->slug }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $category->posts_count }}</span>
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $category->sort_order }}</span>
                                </td>
                                <td class="text-muted">
                                    {{ $category->created_at->format('Y-m-d') }}
                                    <br>
                                    <small>{{ $category->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.categories.show', $category) }}" 
                                           class="btn btn-outline-info btn-sm" 
                                           title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a href="{{ route('admin.categories.edit', $category) }}" 
                                           class="btn btn-outline-warning btn-sm" 
                                           title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm" 
                                                title="حذف"
                                                onclick="confirmDelete({{ $category->id }}, '{{ $category->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Hidden Delete Form -->
                                    <form id="delete-form-{{ $category->id }}" 
                                          action="{{ route('admin.categories.destroy', $category) }}" 
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
            @if($categories->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $categories->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">لا توجد أقسام حتى الآن</h4>
                <p class="text-muted mb-4">ابدأ بإضافة أول قسم لتنظيم المحتوى</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إضافة قسم جديد
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
@endpush

@section('scripts')
<!-- jQuery UI for drag and drop -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
function confirmDelete(categoryId, categoryName) {
    if (confirm('هل أنت متأكد من حذف القسم "' + categoryName + '"؟\n\nتحذير: سيتم حذف جميع المواضيع المرتبطة بهذا القسم أيضاً!')) {
        document.getElementById('delete-form-' + categoryId).submit();
    }
}

// تفعيل السحب والإفلات
$(document).ready(function() {
    $('#sortable-categories').sortable({
        handle: '.drag-handle',
        placeholder: 'ui-state-highlight',
        helper: function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        },
        update: function(event, ui) {
            var categoryIds = [];
            $('#sortable-categories tr').each(function(index) {
                categoryIds.push({
                    id: $(this).data('id'),
                    sort_order: index + 1
                });
            });
            
            // إرسال الترتيب الجديد للخادم
            $.ajax({
                url: '{{ route("admin.categories.reorder") }}',
                method: 'POST',
                data: {
                    categories: categoryIds,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // تحديث أرقام الترتيب في الجدول
                        $('#sortable-categories tr').each(function(index) {
                            $(this).find('td:nth-child(8) .badge').text(index + 1);
                        });
                        
                        // إظهار رسالة نجاح
                        showAlert('تم تحديث ترتيب الأقسام بنجاح', 'success');
                    }
                },
                error: function() {
                    showAlert('حدث خطأ أثناء تحديث الترتيب', 'error');
                    // إعادة تحميل الصفحة في حالة الخطأ
                    location.reload();
                }
            });
        }
    });
    
    // إضافة أنماط CSS للسحب والإفلات
    $('<style>').text(`
        .ui-state-highlight {
            height: 60px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
        }
        .ui-sortable-helper {
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .drag-handle:hover {
            color: #007bff !important;
        }
    `).appendTo('head');
});

// دالة لإظهار التنبيهات
function showAlert(message, type) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
    
    $('.card-header').after(alertHtml);
    
    // إخفاء التنبيه بعد 3 ثوان
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
}
</script>
@endsection