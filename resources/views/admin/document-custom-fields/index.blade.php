@extends('admin.layouts.app')

@section('title', 'إدارة الحقول المخصصة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> إدارة الحقول المخصصة
                        @if(request('section'))
                            @php
                                $section = \App\Models\DocumentSection::find(request('section'));
                            @endphp
                            @if($section)
                                - {{ $section->name }}
                            @endif
                        @endif
                    </h3>
                    <div>
                        @if(request('section'))
                            <a href="{{ route('admin.document-custom-fields.create', ['section' => request('section')]) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة حقل جديد
                            </a>
                            <a href="{{ route('admin.document-sections.show', request('section')) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> العودة للقسم
                            </a>
                        @else
                            <a href="{{ route('admin.document-custom-fields.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة حقل جديد
                            </a>
                        @endif
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

                    <!-- فلاتر البحث -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <select class="form-select" id="sectionFilter">
                                <option value="">جميع الأقسام</option>
                                @foreach($sections as $sec)
                                    <option value="{{ $sec->id }}" {{ request('section') == $sec->id ? 'selected' : '' }}>
                                        {{ $sec->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="typeFilter">
                                <option value="">جميع الأنواع</option>
                                <option value="text">نص</option>
                                <option value="textarea">نص طويل</option>
                                <option value="number">رقم</option>
                                <option value="date">تاريخ</option>
                                <option value="select">قائمة منسدلة</option>
                                <option value="multiselect">اختيارات متعددة</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">جميع الحالات</option>
                                <option value="1">مفعل</option>
                                <option value="0">غير مفعل</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="searchInput" placeholder="البحث في الحقول...">
                        </div>
                    </div>

                    @if($fields->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="fieldsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">الترتيب</th>
                                        <th>اسم الحقل</th>
                                        <th>القسم</th>
                                        <th>النوع</th>
                                        <th>مطلوب</th>
                                        <th>الحالة</th>
                                        <th width="200">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-fields">
                                    @foreach($fields as $field)
                                    <tr data-id="{{ $field->id }}" data-section="{{ $field->section_id }}" data-type="{{ $field->type }}" data-status="{{ $field->is_active ? '1' : '0' }}">
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
                                            <a href="{{ route('admin.document-sections.show', $field->section) }}" class="text-decoration-none">
                                                {{ $field->section->name }}
                                            </a>
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
                                                <span class="badge bg-light text-dark">اختياري</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" type="checkbox" 
                                                       data-id="{{ $field->id }}" 
                                                       {{ $field->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.document-custom-fields.show', $field) }}" 
                                                   class="btn btn-outline-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.document-custom-fields.edit', $field) }}" 
                                                   class="btn btn-outline-primary" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($field->fieldValues()->count() == 0)
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteField({{ $field->id }}, '{{ $field->label }}')" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            title="لا يمكن حذف الحقل لأنه يحتوي على بيانات" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $fields->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد حقول مخصصة</h5>
                            <p class="text-muted">
                                @if(request('section'))
                                    لم يتم إضافة أي حقول مخصصة لهذا القسم بعد
                                @else
                                    لم يتم إضافة أي حقول مخصصة بعد
                                @endif
                            </p>
                            @if(request('section'))
                                <a href="{{ route('admin.document-custom-fields.create', ['section' => request('section')]) }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة أول حقل مخصص
                                </a>
                            @else
                                <a href="{{ route('admin.document-custom-fields.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة أول حقل مخصص
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal حذف الحقل -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> تأكيد حذف الحقل
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">هل أنت متأكد من حذف الحقل <strong id="fieldName"></strong>؟</p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <strong>تنبيه:</strong> هذا الإجراء لا يمكن التراجع عنه وسيتم حذف:
                    <ul class="mb-0 mt-2">
                        <li>الحقل المخصص نفسه</li>
                        <li>جميع القيم المرتبطة بهذا الحقل في الوثائق</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> إلغاء
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> نعم، احذف الحقل
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    // تفعيل السحب والإفلات لإعادة الترتيب
    if (document.getElementById('sortable-fields')) {
        new Sortable(document.getElementById('sortable-fields'), {
            handle: '.sort-handle',
            animation: 150,
            onEnd: function(evt) {
                updateFieldOrder();
            }
        });
    }
    
    // تحديث ترتيب الحقول
    function updateFieldOrder() {
        const rows = $('#sortable-fields tr');
        const order = [];
        
        rows.each(function(index) {
            order.push({
                id: $(this).data('id'),
                sort_order: index + 1
            });
        });
        
        $.ajax({
            url: '{{ route("admin.document-custom-fields.reorder") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order: order
            },
            success: function(response) {
                if (response.success) {
                    // تحديث أرقام الترتيب في الواجهة
                    rows.each(function(index) {
                        $(this).find('.sort-handle').text((index + 1));
                    });
                    
                    showToast('تم تحديث ترتيب الحقول بنجاح', 'success');
                }
            },
            error: function() {
                showToast('حدث خطأ أثناء تحديث الترتيب', 'error');
            }
        });
    }
    
    // تبديل حالة الحقل
    $('.status-toggle').on('change', function() {
        const fieldId = $(this).data('id');
        const isActive = $(this).is(':checked');
        
        $.ajax({
            url: `/admin/document-custom-fields/${fieldId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                is_active: isActive ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                } else {
                    // إعادة تعيين الحالة في حالة الفشل
                    $(this).prop('checked', !isActive);
                    showToast('حدث خطأ أثناء تحديث الحالة', 'error');
                }
            }.bind(this),
            error: function() {
                // إعادة تعيين الحالة في حالة الفشل
                $(this).prop('checked', !isActive);
                showToast('حدث خطأ أثناء تحديث الحالة', 'error');
            }.bind(this)
        });
    });
    
    // فلترة الجدول
    function filterTable() {
        const sectionFilter = $('#sectionFilter').val();
        const typeFilter = $('#typeFilter').val();
        const statusFilter = $('#statusFilter').val();
        const searchText = $('#searchInput').val().toLowerCase();
        
        $('#fieldsTable tbody tr').each(function() {
            const row = $(this);
            const section = row.data('section').toString();
            const type = row.data('type');
            const status = row.data('status').toString();
            const text = row.text().toLowerCase();
            
            let show = true;
            
            if (sectionFilter && section !== sectionFilter) show = false;
            if (typeFilter && type !== typeFilter) show = false;
            if (statusFilter && status !== statusFilter) show = false;
            if (searchText && !text.includes(searchText)) show = false;
            
            row.toggle(show);
        });
    }
    
    // ربط أحداث الفلترة
    $('#sectionFilter, #typeFilter, #statusFilter').on('change', filterTable);
    $('#searchInput').on('input', filterTable);
    
    // تحديث URL عند تغيير فلتر القسم (بدون إعادة تحميل الصفحة)
    $('#sectionFilter').on('change', function() {
        const sectionId = $(this).val();
        const url = new URL(window.location);
        if (sectionId) {
            url.searchParams.set('section', sectionId);
        } else {
            url.searchParams.delete('section');
        }
        window.history.pushState({}, '', url);
    });
});

// حذف الحقل
function deleteField(fieldId, fieldName) {
    $('#fieldName').text(fieldName);
    $('#deleteForm').attr('action', `/admin/document-custom-fields/${fieldId}`);
    $('#deleteModal').modal('show');
}

// عرض رسائل التنبيه
function showToast(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const toast = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="fas ${icon}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => {
        toast.alert('close');
    }, 3000);
}
</script>
@endpush

@push('styles')
<style>
.sort-handle {
    cursor: move;
    user-select: none;
}

.sortable-ghost {
    opacity: 0.4;
}

.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush