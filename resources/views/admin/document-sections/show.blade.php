@extends('admin.layouts.app')

@section('title', 'عرض القسم: ' . $section->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- معلومات القسم -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-folder-open"></i> {{ $section->name }}
                        @if(!$section->is_active)
                            <span class="badge bg-warning ms-2">غير مفعل</span>
                        @else
                            <span class="badge bg-success ms-2">مفعل</span>
                        @endif
                    </h3>
                    <div>
                        <a href="{{ route('admin.document-sections.edit', $section) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('admin.document-sections.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> العودة
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h5>معلومات القسم</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold" style="width: 150px;">اسم القسم:</td>
                                        <td>{{ $section->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">الرابط:</td>
                                        <td><code>{{ $section->slug }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">الوصف:</td>
                                        <td>{{ $section->description ?: 'لا يوجد وصف' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">ترتيب العرض:</td>
                                        <td>{{ $section->sort_order }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">الحالة:</td>
                                        <td>
                                            @if($section->is_active)
                                                <span class="badge bg-success">مفعل</span>
                                            @else
                                                <span class="badge bg-warning">غير مفعل</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">تاريخ الإنشاء:</td>
                                        <td>{{ $section->created_at->format('Y/m/d H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">آخر تحديث:</td>
                                        <td>{{ $section->updated_at->format('Y/m/d H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- إحصائيات القسم -->
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">إحصائيات القسم</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h3 class="text-primary mb-1">{{ $section->documents_count ?? 0 }}</h3>
                                                <small class="text-muted">الوثائق</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h3 class="text-success mb-1">{{ $section->custom_fields_count ?? 0 }}</h3>
                                            <small class="text-muted">الحقول المخصصة</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- روابط سريعة -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">إجراءات سريعة</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.document-custom-fields.index', ['section' => $section->id]) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-cogs"></i> إدارة الحقول المخصصة
                                        </a>
                                        <a href="{{ route('admin.documents.index', ['section' => $section->id]) }}" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-file-alt"></i> إدارة الوثائق
                                        </a>
                                        <a href="{{ route('admin.documents.create', ['section' => $section->id]) }}" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-plus"></i> إضافة وثيقة جديدة
                                        </a>
                                        <a href="{{ route('admin.document-custom-fields.create', ['section' => $section->id]) }}" 
                                           class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-plus-circle"></i> إضافة حقل مخصص
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- الحقول المخصصة -->
            @if($section->customFields && $section->customFields->count() > 0)
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs"></i> الحقول المخصصة ({{ $section->customFields->count() }})
                    </h5>
                    <a href="{{ route('admin.document-custom-fields.create', ['section' => $section->id]) }}" 
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> إضافة حقل جديد
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>الترتيب</th>
                                    <th>اسم الحقل</th>
                                    <th>النوع</th>
                                    <th>مطلوب</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($section->customFields->sortBy('sort_order') as $field)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $field->sort_order }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $field->label }}</strong>
                                        <br><small class="text-muted">{{ $field->name }}</small>
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
                                        @if($field->is_active)
                                            <span class="badge bg-success">مفعل</span>
                                        @else
                                            <span class="badge bg-warning">غير مفعل</span>
                                        @endif
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
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card mt-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد حقول مخصصة</h5>
                    <p class="text-muted">لم يتم إضافة أي حقول مخصصة لهذا القسم بعد</p>
                    <a href="{{ route('admin.document-custom-fields.create', ['section' => $section->id]) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة أول حقل مخصص
                    </a>
                </div>
            </div>
            @endif
            
            <!-- الوثائق الحديثة -->
            @if($section->documents && $section->documents->count() > 0)
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt"></i> الوثائق الحديثة
                    </h5>
                    <a href="{{ route('admin.documents.index', ['section' => $section->id]) }}" 
                       class="btn btn-outline-primary btn-sm">
                        عرض جميع الوثائق ({{ $section->documents_count }})
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>العنوان</th>
                                    <th>الحالة</th>
                                    <th>المشاهدات</th>
                                    <th>تاريخ النشر</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($section->documents->take(5) as $document)
                                <tr>
                                    <td>
                                        <strong>{{ $document->title }}</strong>
                                        @if($document->is_featured)
                                            <span class="badge bg-warning ms-1">مميز</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($document->is_published)
                                            <span class="badge bg-success">منشور</span>
                                        @else
                                            <span class="badge bg-warning">مسودة</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $document->views_count }}</span>
                                    </td>
                                    <td>
                                        {{ $document->published_at ? $document->published_at->format('Y/m/d') : 'غير منشور' }}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.documents.show', $document) }}" 
                                               class="btn btn-outline-info" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.documents.edit', $document) }}" 
                                               class="btn btn-outline-primary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card mt-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد وثائق</h5>
                    <p class="text-muted">لم يتم إضافة أي وثائق لهذا القسم بعد</p>
                    <a href="{{ route('admin.documents.create', ['section' => $section->id]) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة أول وثيقة
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-end {
    border-right: 1px solid #dee2e6 !important;
}

.card.bg-light {
    border: 1px solid #dee2e6;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endpush