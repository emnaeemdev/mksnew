@extends('admin.layouts.app')

@section('title', 'إدارة الوثائق')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إدارة الوثائق</h3>
                    <div>
                        <a href="{{ route('admin.documents.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة وثيقة جديدة
                        </a>
                    </div>
                </div>
                
                <!-- فلاتر البحث -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.documents.index') }}" id="filterForm">
                        @if(request('section_id'))
                            <input type="hidden" name="section_id" value="{{ request('section_id') }}">
                        @endif
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">البحث</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="البحث في العنوان والمحتوى...">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="section" class="form-label">القسم</label>
                                <select class="form-select" id="section" name="section">
                                    <option value="">جميع الأقسام</option>
                                    @foreach($sections as $section)
                                        @php
                                            $selectedSectionId = request('section_id') ?: request('section');
                                        @endphp
                                        <option value="{{ $section->id }}" 
                                                {{ $selectedSectionId == $section->id ? 'selected' : '' }}>
                                            {{ $section->name }} ({{ $section->documents_count }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="status" class="form-label">الحالة</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">جميع الحالات</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="featured" class="form-label">المميز</label>
                                <select class="form-select" id="featured" name="featured">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('featured') == '1' ? 'selected' : '' }}>مميز</option>
                                    <option value="0" {{ request('featured') == '0' ? 'selected' : '' }}>غير مميز</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="sort" class="form-label">الترتيب</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>الأحدث</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>الأقدم</option>
                                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>العنوان</option>
                                    <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>الأكثر مشاهدة</option>
                                </select>
                            </div>
                            
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        @if($customFields && $customFields->count() > 0)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">فلاتر الحقول المخصصة</h6>
                                </div>
                                @foreach($customFields as $field)
                                    <div class="col-md-3 mb-3">
                                        <label for="custom_field_{{ $field->id }}" class="form-label">{{ $field->label }}</label>
                                        @if($field->type === 'select')
                                            <select class="form-select" id="custom_field_{{ $field->id }}" name="custom_fields[{{ $field->id }}]">
                                                <option value="">اختر...</option>
                                                @if(isset($field->options_with_counts))
                                                    @foreach($field->options_with_counts as $optionData)
                                                        <option value="{{ $optionData['value'] }}" {{ request("custom_fields.{$field->id}") == $optionData['value'] ? 'selected' : '' }}>
                                                            {{ $optionData['label'] }} ({{ $optionData['count'] }})
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach($field->options as $option)
                                                        <option value="{{ $option }}" {{ request("custom_fields.{$field->id}") == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @elseif($field->type === 'multiselect')
                                            <select class="form-select" id="custom_field_{{ $field->id }}" name="custom_fields[{{ $field->id }}]">
                                                <option value="">اختر...</option>
                                                @if(isset($field->options_with_counts))
                                                    @foreach($field->options_with_counts as $optionData)
                                                        <option value="{{ $optionData['value'] }}" {{ request("custom_fields.{$field->id}") == $optionData['value'] ? 'selected' : '' }}>
                                                            {{ $optionData['label'] }} ({{ $optionData['count'] }})
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach($field->options as $option)
                                                        <option value="{{ $option }}" {{ request("custom_fields.{$field->id}") == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @else
                                            <input type="text" class="form-control" id="custom_field_{{ $field->id }}" name="custom_fields[{{ $field->id }}]" value="{{ request("custom_fields.{$field->id}") }}" placeholder="البحث في {{ $field->label }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        @if(request()->hasAny(['search', 'section_id', 'status', 'featured', 'sort', 'custom_fields']))
                            <div class="mt-3">
                                <a href="{{ route('admin.documents.index', request('section_id') ? ['section_id' => request('section_id')] : []) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times"></i> مسح الفلاتر
                                </a>
                            </div>
                        @endif
                    </form>
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
                    
                    <!-- إحصائيات سريعة -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['total'] }}</h3>
                                    <small>إجمالي الوثائق</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['published'] }}</h3>
                                    <small>منشور</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['featured'] }}</h3>
                                    <small>مميز</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['draft'] }}</h3>
                                    <small>مسودة</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($documents->count() > 0)
                        <!-- جدول الوثائق -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th width="40%">الوثيقة</th>
                                        <th width="15%">القسم</th>
                                        <th width="10%">الحالة</th>
                                        <th width="10%">المشاهدات</th>
                                        <th width="10%">التاريخ</th>
                                        <th width="10%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input document-checkbox" 
                                                       value="{{ $document->id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-start">
                                                    <div>
                                                        
                                                    </div>
                                                    
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">
                                                            <a href="{{ route('admin.documents.show', $document) }}" 
                                                               class="text-decoration-none">
                                                                {{ $document->title }}
                                                            </a>
                                                            @if($document->is_featured)
                                                                <span class="badge bg-warning text-dark ms-1">مميز</span>
                                                            @endif
                                                        </h6>
                                                        @if($document->excerpt)
                                                            <p class="text-muted mb-1 small">{{ Str::limit($document->excerpt, 100) }}</p>
                                                        @endif
                                                        <small class="text-muted">
                                                            بواسطة: {{ $document->user->name ?? 'غير محدد' }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $document->section->name }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-{{ $document->is_published ? 'success' : 'secondary' }} me-2">
                                                        {{ $document->is_published ? 'منشور' : 'مسودة' }}
                                                    </span>
                                                    @if($document->is_published && $document->published_at)
                                                        <small class="text-muted">
                                                            
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ number_format($document->views_count) }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $document->published_at->format('Y-m-d') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.documents.show', $document) }}" 
                                                       class="btn btn-outline-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.documents.edit', $document) }}" 
                                                       class="btn btn-outline-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                                                                data-bs-toggle="dropdown">
                                                            <span class="visually-hidden">Toggle Dropdown</span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <form action="{{ route('admin.documents.toggle-status', $document) }}" 
                                                                      method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-{{ $document->is_published ? 'eye-slash' : 'eye' }}"></i>
                                                                        {{ $document->is_published ? 'إلغاء النشر' : 'نشر' }}
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('admin.documents.toggle-featured', $document) }}" 
                                                                      method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-{{ $document->is_featured ? 'star-o' : 'star' }}"></i>
                                                                        {{ $document->is_featured ? 'إلغاء التمييز' : 'تمييز' }}
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <button type="button" class="dropdown-item text-danger" 
                                                                        onclick="confirmDelete({{ $document->id }}, '{{ $document->title }}')">
                                                                    <i class="fas fa-trash"></i> حذف
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- إجراءات مجمعة -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center" id="bulkActions" style="display: none !important;">
                                    <span class="me-3">الإجراءات المجمعة:</span>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-success" onclick="bulkAction('publish')">
                                            <i class="fas fa-eye"></i> نشر
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="bulkAction('unpublish')">
                                            <i class="fas fa-eye-slash"></i> إلغاء النشر
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" onclick="bulkAction('feature')">
                                            <i class="fas fa-star"></i> تمييز
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="bulkAction('delete')">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $documents->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">لا توجد وثائق</h4>
                            @if(request()->hasAny(['search', 'section', 'status', 'featured']))
                                <p class="text-muted">لم يتم العثور على وثائق تطابق معايير البحث.</p>
                                <a href="{{ route('admin.documents.index', request('section_id') ? ['section_id' => request('section_id')] : []) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-times"></i> مسح الفلاتر
                                </a>
                            @else
                                <p class="text-muted">ابدأ بإضافة وثيقة جديدة.</p>
                                <a href="{{ route('admin.documents.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة وثيقة جديدة
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال تأكيد الحذف -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تأكيد حذف الوثيقة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف الوثيقة "<strong id="documentTitle"></strong>"؟</p>
                <p class="text-danger">هذا الإجراء لا يمكن التراجع عنه.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> حذف نهائياً
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- مودال الإجراءات المجمعة -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionTitle">تأكيد الإجراء</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="bulkActionMessage"></p>
                <p class="text-warning">سيتم تطبيق هذا الإجراء على <strong id="selectedCount"></strong> وثيقة.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="confirmBulkAction">
                    <i class="fas fa-check"></i> تأكيد
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // تحديث الفلاتر تلقائياً
    $('#search, #section, #status, #featured, #sort').on('change', function() {
        $('#filterForm').submit();
    });
    
    // اختيار الكل
    $('#selectAll').on('change', function() {
        $('.document-checkbox').prop('checked', $(this).is(':checked'));
        toggleBulkActions();
    });
    
    // اختيار فردي
    $(document).on('change', '.document-checkbox', function() {
        const totalCheckboxes = $('.document-checkbox').length;
        const checkedCheckboxes = $('.document-checkbox:checked').length;
        
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        toggleBulkActions();
    });
    
    // إظهار/إخفاء الإجراءات المجمعة
    function toggleBulkActions() {
        const checkedCount = $('.document-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkActions').show();
        } else {
            $('#bulkActions').hide();
        }
    }
});

// تأكيد حذف وثيقة واحدة
function confirmDelete(documentId, documentTitle) {
    $('#documentTitle').text(documentTitle);
    $('#deleteForm').attr('action', `/admin/documents/${documentId}`);
    $('#deleteModal').modal('show');
}

// الإجراءات المجمعة
function bulkAction(action) {
    const selectedIds = $('.document-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        alert('يرجى اختيار وثيقة واحدة على الأقل');
        return;
    }
    
    let title, message, actionUrl;
    
    switch (action) {
        case 'publish':
            title = 'نشر الوثائق';
            message = 'هل تريد نشر الوثائق المحددة؟';
            actionUrl = '{{ route("admin.documents.bulk-publish") }}';
            break;
        case 'unpublish':
            title = 'إلغاء نشر الوثائق';
            message = 'هل تريد إلغاء نشر الوثائق المحددة؟';
            actionUrl = '{{ route("admin.documents.bulk-unpublish") }}';
            break;
        case 'feature':
            title = 'تمييز الوثائق';
            message = 'هل تريد تمييز الوثائق المحددة؟';
            actionUrl = '{{ route("admin.documents.bulk-feature") }}';
            break;
        case 'delete':
            title = 'حذف الوثائق';
            message = 'هل أنت متأكد من حذف الوثائق المحددة؟ هذا الإجراء لا يمكن التراجع عنه.';
            actionUrl = '{{ route("admin.documents.bulk-delete") }}';
            break;
    }
    
    $('#bulkActionTitle').text(title);
    $('#bulkActionMessage').text(message);
    $('#selectedCount').text(selectedIds.length);
    
    $('#confirmBulkAction').off('click').on('click', function() {
        // إنشاء نموذج وإرساله
        const form = $('<form>', {
            method: 'POST',
            action: actionUrl
        });
        
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: $('meta[name="csrf-token"]').attr('content')
        }));
        
        selectedIds.forEach(function(id) {
            form.append($('<input>', {
                type: 'hidden',
                name: 'document_ids[]',
                value: id
            }));
        });
        
        $('body').append(form);
        form.submit();
    });
    
    $('#bulkActionModal').modal('show');
}

// التحديث التلقائي للفلاتر
$(document).ready(function() {
    // إضافة مستمع للتغييرات في حقول الفلتر
    $('#filterForm select, #filterForm input[type="text"]').on('change input', function() {
        // تأخير قصير للبحث النصي
        if ($(this).attr('type') === 'text') {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(function() {
                $('#filterForm').submit();
            }, 500);
        } else {
            // تطبيق فوري للقوائم المنسدلة
            $('#filterForm').submit();
        }
    });
    
    // منع الإرسال المتكرر
    $('#filterForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
    });
});
</script>
@endpush

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75em;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.card.bg-primary,
.card.bg-success,
.card.bg-warning,
.card.bg-info {
    transition: transform 0.2s;
}

.card.bg-primary:hover,
.card.bg-success:hover,
.card.bg-warning:hover,
.card.bg-info:hover {
    transform: translateY(-2px);
}

.table-responsive {
    border-radius: 0.375rem;
}

.dropdown-menu {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
</style>
@endpush