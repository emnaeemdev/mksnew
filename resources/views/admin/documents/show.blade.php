@extends('admin.layouts.app')

@section('title', 'عرض الوثيقة: ' . $document->title)


<body>
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- رأس الصفحة -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-1">{{ $document->title }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $document->is_published ? 'success' : 'secondary' }}">
                                {{ $document->is_published ? 'منشور' : 'مسودة' }}
                            </span>
                            @if($document->is_featured)
                                <span class="badge bg-warning text-dark">مميز</span>
                            @endif
                            <span class="badge bg-info">{{ $document->section->name }}</span>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <a href="{{ route('admin.documents.edit', $document) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        
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
                                <a href="{{ route('admin.documents.index') }}" class="dropdown-item">
                                    <i class="fas fa-list"></i> العودة للقائمة
                                </a>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item text-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> حذف
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- المحتوى الرئيسي -->
                <div class="col-lg-8">
                    <!-- معلومات الوثيقة -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">معلومات الوثيقة</h5>
                        </div>
                        <div class="card-body">

                            
                            @if($document->excerpt)
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">الملخص:</h6>
                                    <p class="mb-0">{{ $document->excerpt }}</p>
                                </div>
                            @endif
                            
                            <div class="document-content">
                                {!! $document->content !!}
                            </div>
                        </div>
                    </div>
                    
                    
                    <!-- الملفات المرفقة -->
                    @if($document->files->count() > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">الملفات المرفقة</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($document->files as $file)
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 h-100 d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="{{ $file->getIcon() }} fa-2x text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $file->display_name }}</h6>
                                                    <small class="text-muted d-block">{{ $file->original_name }}</small>
                                                    <small class="text-muted">{{ $file->getFormattedSize() }}</small>
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.documents.files.download', $file) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- الشريط الجانبي -->
                <div class="col-lg-4">
                    <!-- إحصائيات سريعة -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">إحصائيات</h5>
                        </div>
                        <div class="card-body">
                            <!-- الحقول المخصصة -->
                    @if($document->fieldValues->count() > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                 
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($document->fieldValues as $fieldValue)
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 h-100">
                                                <h6 class="text-muted mb-2">{{ $fieldValue->field->label }}</h6>
                
                @switch($fieldValue->field->type)
                                                    @case('date')
                                                        <p class="mb-0">
                                                            <i class="fas fa-calendar text-primary me-2"></i>
                                                            {{ \Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d') }}
                                                        </p>
                                                        @break
                                                        
                                                    @case('number')
                                                        <p class="mb-0">
                                                            <i class="fas fa-hashtag text-success me-2"></i>
                                                            {{ number_format($fieldValue->value) }}
                                                        </p>
                                                        @break
                                                        
                                                    @case('select')
                                                        <p class="mb-0">
                                                            <span class="badge bg-secondary">{{ $fieldValue->value }}</span>
                                                        </p>
                                                        @break
                                                        
                                                    @case('multiselect')
                                                        @php
                                                            $values = is_array($fieldValue->value) ? $fieldValue->value : (json_decode($fieldValue->value, true) ?: []);
                                                        @endphp
                                                        <div>
                                                            @foreach($values as $value)
                                                                <span class="badge bg-secondary me-1">{{ $value }}</span>
                                                            @endforeach
                                                        </div>
                                                        @break
                                                        
                                                    @case('file')
                                                        <a href="{{ asset('storage/' . $fieldValue->value) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-download me-2"></i>
                                                            تحميل الملف
                                                        </a>
                                                        @break
                                                        
                                                    @case('textarea')
                                                        <div class="text-break" style="white-space: pre-wrap;">{{ $fieldValue->value }}</div>
                                                        @break
                                                        
                                                    @default
                                                        <p class="mb-0 text-break">{{ $fieldValue->value }}</p>
                                                @endswitch
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h3 class="text-primary mb-1">{{ number_format($document->views_count) }}</h3>
                                        <small class="text-muted">مشاهدة</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h3 class="text-info mb-1">{{ $document->fieldValues->count() }}</h3>
                                    <small class="text-muted">حقل مخصص</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- معلومات النشر -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">معلومات النشر</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">الكاتب</small>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 32px; height: 32px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <span class="fw-bold">{{ $document->user->name ?? 'غير محدد' }}</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">القسم</small>
                                <a href="{{ route('admin.document-sections.show', $document->section) }}" 
                                   class="text-decoration-none">
                                    <span class="badge bg-info">{{ $document->section->name }}</span>
                                </a>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">تاريخ الإنشاء</small>
                                <span>{{ $document->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">آخر تحديث</small>
                                <span>{{ $document->updated_at->format('Y-m-d H:i') }}</span>
                            </div>
                            
                            @if($document->published_at)
                                <div class="mb-3">
                                    <small class="text-muted d-block">تاريخ النشر</small>
                                    <span>{{ $document->published_at->format('Y-m-d H:i') }}</span>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">الرابط</small>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" 
                                           value="{{ route('content.show', [app()->getLocale(), $document->section->name_en ?: $document->section->slug, $document->id]) }}" 
                                           readonly id="documentUrl">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="copyToClipboard('#documentUrl')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- إجراءات سريعة -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">إجراءات سريعة</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.documents.edit', $document) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> تعديل الوثيقة
                                </a>
                                
                                <a href="{{ route('admin.documents.create') }}?section={{ $document->section_id }}" 
                                   class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-plus"></i> إضافة وثيقة جديدة في نفس القسم
                                </a>
                                
                                <a href="{{ route('admin.document-sections.show', $document->section) }}" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-folder"></i> عرض القسم
                                </a>
                                
                                @if($document->is_published)
                                    <a href="{{ route('content.show', [app()->getLocale(), $document->section->name_en ?: $document->section->slug, $document->id]) }}" 
                                       class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> عرض في الموقع
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- وثائق ذات صلة -->
                    @if($relatedDocuments->count() > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">وثائق ذات صلة</h5>
                            </div>
                            <div class="card-body">
                                @foreach($relatedDocuments as $related)
                                    <div class="d-flex align-items-start mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-file-alt text-muted"></i>
                                        </div>
                                        
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="{{ route('admin.documents.show', $related) }}" 
                                                   class="text-decoration-none">
                                                    {{ Str::limit($related->title, 40) }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-eye"></i> {{ number_format($related->views_count) }}
                                                <span class="mx-1">•</span>
                                                {{ $related->updated_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <div class="text-center">
                                    <a href="{{ route('admin.documents.index', ['section' => $document->section_id]) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        عرض جميع وثائق القسم
                                    </a>
                                </div>
                            </div>
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
                <p>هل أنت متأكد من حذف الوثيقة "<strong>{{ $document->title }}</strong>"؟</p>
                <div class="alert alert-warning">
                    <h6>تحذير:</h6>
                    <ul class="mb-0">
                        <li>سيتم حذف جميع البيانات المرتبطة بهذه الوثيقة</li>
                        <li>سيتم حذف جميع قيم الحقول المخصصة ({{ $document->fieldValues->count() }} حقل)</li>
                        <li>سيتم فقدان إحصائيات المشاهدة ({{ number_format($document->views_count) }} مشاهدة)</li>
                        <li>هذا الإجراء لا يمكن التراجع عنه</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.documents.destroy', $document) }}" method="POST" class="d-inline">
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
@endsection

@push('scripts')
<script>
// نسخ الرابط إلى الحافظة
function copyToClipboard(element) {
    const input = document.querySelector(element);
    input.select();
    input.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        
        // إظهار رسالة نجاح
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check text-success"></i>';
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
        }, 2000);
        
    } catch (err) {
        alert('فشل في نسخ الرابط');
    }
}

// تأكيد حذف الوثيقة
function confirmDelete() {
    $('#deleteModal').modal('show');
}

$(document).ready(function() {
    // إضافة تأثيرات hover للبطاقات
    $('.card').hover(
        function() {
            $(this).addClass('shadow-sm');
        },
        function() {
            $(this).removeClass('shadow-sm');
        }
    );
});
</script>

@endpush