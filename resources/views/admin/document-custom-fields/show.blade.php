@extends('admin.layouts.app')

@section('title', 'تفاصيل الحقل المخصص')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- معلومات الحقل -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ $documentCustomField->label }}</h3>
                    <div>
                        <span class="badge bg-{{ $documentCustomField->is_active ? 'success' : 'secondary' }} me-2">
                            {{ $documentCustomField->is_active ? 'مفعل' : 'غير مفعل' }}
                        </span>
                        @if($documentCustomField->is_required)
                            <span class="badge bg-warning me-2">مطلوب</span>
                        @endif
                        <span class="badge bg-info me-2">{{ $documentCustomField->section->name }}</span>
                        
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.document-custom-fields.edit', $documentCustomField) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> تعديل
                            </a>
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle dropdown-toggle-split" 
                                    data-bs-toggle="dropdown">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.document-sections.show', $documentCustomField->section) }}">
                                        <i class="fas fa-folder"></i> عرض القسم
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.document-custom-fields.index', ['section' => $documentCustomField->section_id]) }}">
                                        <i class="fas fa-list"></i> حقول القسم
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.document-custom-fields.toggle-status', $documentCustomField) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-{{ $documentCustomField->is_active ? 'eye-slash' : 'eye' }}"></i>
                                            {{ $documentCustomField->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}
                                        </button>
                                    </form>
                                </li>
                                @if($documentCustomField->fieldValues()->count() == 0)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            <i class="fas fa-trash"></i> حذف الحقل
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        
                        <a href="{{ route('admin.document-custom-fields.index') }}" class="btn btn-secondary btn-sm ms-2">
                            <i class="fas fa-arrow-left"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">معلومات أساسية</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>الاسم التقني:</strong></td>
                                            <td><code>{{ $documentCustomField->name }}</code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>نوع الحقل:</strong></td>
                                            <td>
                                                @switch($documentCustomField->type)
                                                    @case('text')
                                                        <span class="badge bg-primary">نص قصير</span>
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
                                                @endswitch
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>ترتيب العرض:</strong></td>
                                            <td>{{ $documentCustomField->sort_order }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>تاريخ الإنشاء:</strong></td>
                                            <td>{{ $documentCustomField->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                        @if($documentCustomField->updated_at != $documentCustomField->created_at)
                                            <tr>
                                                <td><strong>آخر تحديث:</strong></td>
                                                <td>{{ $documentCustomField->updated_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-muted">إعدادات إضافية</h6>
                                    @if($documentCustomField->placeholder)
                                        <p><strong>النص التوضيحي:</strong><br>
                                        <span class="text-muted">{{ $documentCustomField->placeholder }}</span></p>
                                    @endif
                                    
                                    @if($documentCustomField->help_text)
                                        <p><strong>نص المساعدة:</strong><br>
                                        <span class="text-muted">{{ $documentCustomField->help_text }}</span></p>
                                    @endif
                                    
                                    @if($documentCustomField->validation_rules)
                                        <p><strong>قواعد التحقق:</strong><br>
                                        <code>{{ $documentCustomField->validation_rules }}</code></p>
                                    @endif
                                </div>
                            </div>
                            
                            @if(in_array($documentCustomField->type, ['select', 'multiselect']) && $documentCustomField->options)
                <hr>
                <h6 class="text-muted">خيارات القائمة</h6>
                <div class="row">
                    @foreach($documentCustomField->options as $index => $option)
                        <div class="col-md-4 mb-2">
                            <span class="badge bg-light text-dark border">{{ $index + 1 }}. {{ $option }}</span>
                        </div>
                    @endforeach
                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <!-- إحصائيات سريعة -->
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">إحصائيات الاستخدام</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h3 class="text-primary mb-1">{{ $totalValues }}</h3>
                                            <small class="text-muted">إجمالي القيم</small>
                                        </div>
                                        <div class="col-6">
                                            <h3 class="text-success mb-1">{{ $filledValues }}</h3>
                                            <small class="text-muted">قيم مملوءة</small>
                                        </div>
                                    </div>
                                    
                                    @if($totalValues > 0)
                                        <hr>
                                        <div class="progress mb-2">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: {{ ($filledValues / $totalValues) * 100 }}%">
                                                {{ round(($filledValues / $totalValues) * 100, 1) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">نسبة الملء</small>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- معاينة الحقل -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">معاينة الحقل</h6>
                                </div>
                                <div class="card-body">
                                    <label class="form-label">
                                        {{ $documentCustomField->label }}
                                        @if($documentCustomField->is_required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    
                                    @switch($documentCustomField->type)
                                        @case('text')
                                            <input type="text" class="form-control" 
                                                   placeholder="{{ $documentCustomField->placeholder }}" disabled>
                                            @break
                                        @case('textarea')
                                            <textarea class="form-control" rows="3" 
                                                      placeholder="{{ $documentCustomField->placeholder }}" disabled></textarea>
                                            @break
                                        @case('number')
                                            <input type="number" class="form-control" 
                                                   placeholder="{{ $documentCustomField->placeholder }}" disabled>
                                            @break
                                        @case('date')
                                            <input type="date" class="form-control" disabled>
                                            @break
                                        @case('select')
                                            <select class="form-select" disabled>
                                                <option>اختر...</option>
                                                @if($documentCustomField->options)
                                                    @foreach($documentCustomField->options as $option)
                                                        <option>{{ $option }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @break
                                        @case('multiselect')
                                            <select class="form-select" multiple disabled>
                                                @if($documentCustomField->options)
                                                    @foreach($documentCustomField->options as $option)
                                                        <option>{{ $option }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @break
                                    @endswitch
                                    
                                    @if($documentCustomField->help_text)
                                        <div class="form-text">{{ $documentCustomField->help_text }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($totalValues > 0)
                <!-- تحليل البيانات -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">تحليل البيانات</h5>
                    </div>
                    <div class="card-body">
                        @if(in_array($documentCustomField->type, ['select', 'multiselect']))
                            <!-- إحصائيات الخيارات -->
                            <h6 class="text-muted mb-3">توزيع الخيارات</h6>
                            <div class="row">
                                @foreach($valueStats as $value => $count)
                                    <div class="col-md-4 mb-3">
                                        <div class="card border">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">{{ $value ?: 'فارغ' }}</h6>
                                                        <small class="text-muted">{{ $count }} مرة</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-primary">
                                                            {{ round(($count / $totalValues) * 100, 1) }}%
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="progress mt-2" style="height: 4px;">
                                                    <div class="progress-bar" 
                                                         style="width: {{ ($count / $totalValues) * 100 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($documentCustomField->type == 'date')
                            <!-- إحصائيات التواريخ -->
                            <h6 class="text-muted mb-3">إحصائيات التواريخ</h6>
                            <div class="row">
                                @if(isset($dateStats['earliest']))
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-info">{{ $dateStats['earliest'] }}</h5>
                                            <small class="text-muted">أقدم تاريخ</small>
                                        </div>
                                    </div>
                                @endif
                                @if(isset($dateStats['latest']))
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-success">{{ $dateStats['latest'] }}</h5>
                                            <small class="text-muted">أحدث تاريخ</small>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-primary">{{ $filledValues }}</h5>
                                        <small class="text-muted">تواريخ صحيحة</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-warning">{{ $totalValues - $filledValues }}</h5>
                                        <small class="text-muted">تواريخ فارغة</small>
                                    </div>
                                </div>
                            </div>
                        @elseif($documentCustomField->type == 'number')
                            <!-- إحصائيات الأرقام -->
                            <h6 class="text-muted mb-3">إحصائيات الأرقام</h6>
                            <div class="row">
                                @if(isset($numberStats['min']))
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <h5 class="text-info">{{ $numberStats['min'] }}</h5>
                                            <small class="text-muted">أقل قيمة</small>
                                        </div>
                                    </div>
                                @endif
                                @if(isset($numberStats['max']))
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <h5 class="text-success">{{ $numberStats['max'] }}</h5>
                                            <small class="text-muted">أكبر قيمة</small>
                                        </div>
                                    </div>
                                @endif
                                @if(isset($numberStats['avg']))
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <h5 class="text-primary">{{ round($numberStats['avg'], 2) }}</h5>
                                            <small class="text-muted">المتوسط</small>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-primary">{{ $filledValues }}</h5>
                                        <small class="text-muted">أرقام صحيحة</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-warning">{{ $totalValues - $filledValues }}</h5>
                                        <small class="text-muted">قيم فارغة</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- إحصائيات النصوص -->
                            <h6 class="text-muted mb-3">إحصائيات النصوص</h6>
                            <div class="row">
                                @if(isset($textStats['avg_length']))
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-info">{{ round($textStats['avg_length']) }}</h5>
                                            <small class="text-muted">متوسط الطول</small>
                                        </div>
                                    </div>
                                @endif
                                @if(isset($textStats['min_length']))
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-success">{{ $textStats['min_length'] }}</h5>
                                            <small class="text-muted">أقصر نص</small>
                                        </div>
                                    </div>
                                @endif
                                @if(isset($textStats['max_length']))
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-primary">{{ $textStats['max_length'] }}</h5>
                                            <small class="text-muted">أطول نص</small>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-warning">{{ $totalValues - $filledValues }}</h5>
                                        <small class="text-muted">نصوص فارغة</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- الوثائق المرتبطة -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">الوثائق المرتبطة ({{ $documents->total() }})</h5>
                        <div>
                            <a href="{{ route('admin.documents.index', ['section' => $documentCustomField->section_id]) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt"></i> عرض جميع وثائق القسم
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($documents->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الوثيقة</th>
                                            <th>قيمة الحقل</th>
                                            <th>الحالة</th>
                                            <th>تاريخ التحديث</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documents as $document)
                                            @php
                                                $fieldValue = $document->fieldValues->where('field_id', $documentCustomField->id)->first();
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong>{{ $document->title }}</strong>
                                                        @if($document->is_featured)
                                                            <span class="badge bg-warning text-dark ms-1">مميز</span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ Str::limit($document->excerpt, 60) }}</small>
                                                </td>
                                                <td>
                                                    @if($fieldValue && $fieldValue->value)
                                                        @if($documentCustomField->type == 'date')
                                                            {{ \Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d') }}
                                                        @elseif(in_array($documentCustomField->type, ['select', 'multiselect']))
                                                            @if(is_array($fieldValue->value))
                                                                @foreach($fieldValue->value as $val)
                                                                    <span class="badge bg-secondary me-1">{{ $val }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="badge bg-secondary">{{ $fieldValue->value }}</span>
                                                            @endif
                                                        @else
                                                            {{ Str::limit($fieldValue->value, 50) }}
                                                        @endif
                                                    @else
                                                        <span class="text-muted">فارغ</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $document->is_published ? 'success' : 'secondary' }}">
                                                        {{ $document->is_published ? 'منشور' : 'مسودة' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>{{ $document->updated_at->format('Y-m-d H:i') }}</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.documents.show', $document) }}" 
                                                           class="btn btn-outline-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.documents.edit', $document) }}" 
                                                           class="btn btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $documents->links() }}
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">لا توجد وثائق مرتبطة</h5>
                                <p class="text-muted">لم يتم استخدام هذا الحقل في أي وثيقة بعد.</p>
                                <a href="{{ route('admin.documents.create', ['section' => $documentCustomField->section_id]) }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة وثيقة جديدة
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- لا توجد بيانات -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-line fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">لا توجد بيانات للتحليل</h4>
                        <p class="text-muted mb-4">لم يتم استخدام هذا الحقل في أي وثيقة بعد.</p>
                        <div>
                            <a href="{{ route('admin.documents.create', ['section' => $documentCustomField->section_id]) }}" class="btn btn-primary me-2">
                                <i class="fas fa-plus"></i> إضافة وثيقة جديدة
                            </a>
                            <a href="{{ route('admin.document-custom-fields.edit', $documentCustomField) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-edit"></i> تعديل الحقل
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- مودال تأكيد الحذف -->
@if($documentCustomField->fieldValues()->count() == 0)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تأكيد حذف الحقل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف الحقل المخصص "<strong>{{ $documentCustomField->label }}</strong>"؟</p>
                <p class="text-danger">هذا الإجراء لا يمكن التراجع عنه.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.document-custom-fields.destroy', $documentCustomField) }}" method="POST" class="d-inline">
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
@endif
@endsection

@push('styles')
<style>
.progress {
    height: 8px;
}

.card.border {
    transition: all 0.3s ease;
}

.card.border:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transform: translateY(-1px);
}

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
</style>
@endpush