@extends('admin.layouts.app')

@section('title', $sectionData->title . ' - ' . $section->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ $sectionData->title }}</h3>
                    <div>
                        <a href="{{ route('admin.section-data.edit', [$section, $sectionData]) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> تحرير
                        </a>
                        <a href="{{ route('admin.section-data.index', $section) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- معلومات أساسية -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-primary">المعلومات الأساسية</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 30%;">العنوان:</td>
                                    <td>{{ $sectionData->title }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">الرابط:</td>
                                    <td><code>{{ $sectionData->slug }}</code></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">الحالة:</td>
                                    <td>
                                        @switch($sectionData->status)
                                            @case('published')
                                                <span class="badge bg-success">منشور</span>
                                                @break
                                            @case('draft')
                                                <span class="badge bg-warning">مسودة</span>
                                                @break
                                            @case('archived')
                                                <span class="badge bg-secondary">مؤرشف</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $sectionData->status }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">تاريخ النشر:</td>
                                    <td>
                                        @if($sectionData->published_at)
                                            {{ $sectionData->published_at->format('Y-m-d H:i') }}
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">المؤلف:</td>
                                    <td>{{ $sectionData->user->name ?? 'غير محدد' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary">معلومات إضافية</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 30%;">القسم:</td>
                                    <td>
                                        <a href="{{ route('admin.sections.show', $section) }}" class="text-decoration-none">
                                            @if($section->icon)
                                                <i class="{{ $section->icon }}"></i>
                                            @endif
                                            {{ $section->name }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">تاريخ الإنشاء:</td>
                                    <td>{{ $sectionData->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">آخر تحديث:</td>
                                    <td>{{ $sectionData->updated_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @if($sectionData->deleted_at)
                                    <tr>
                                        <td class="fw-bold">تاريخ الحذف:</td>
                                        <td class="text-danger">{{ $sectionData->deleted_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- الحقول المخصصة -->
                    @if($section->customFields->count() > 0)
                        <hr>
                        <h5 class="text-primary mb-3">الحقول المخصصة</h5>
                        
                        <div class="row">
                            @foreach($section->customFields as $field)
                                @php
                                    $fieldValue = $sectionData->getFieldValue($field->id);
                                @endphp
                                @if($fieldValue !== null && $fieldValue !== '')
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-light">
                                            <div class="card-body p-3">
                                                <h6 class="card-title text-secondary mb-2">
                                                    {{ $field->label }}
                                                    <small class="text-muted">({{ $field->getTypeLabel() }})</small>
                                                </h6>
                                                <div class="card-text">
                                                    @switch($field->type)
                                                        @case('textarea')
                                                            <div class="border rounded p-2 bg-light">
                                                                {!! nl2br(e($fieldValue)) !!}
                                                            </div>
                                                            @break
                                                        
                                                        @case('url')
                                                            <a href="{{ $fieldValue }}" target="_blank" class="text-decoration-none">
                                                                {{ $fieldValue }} <i class="fas fa-external-link-alt fa-sm"></i>
                                                            </a>
                                                            @break
                                                        
                                                        @case('email')
                                                            <a href="mailto:{{ $fieldValue }}" class="text-decoration-none">
                                                                {{ $fieldValue }} <i class="fas fa-envelope fa-sm"></i>
                                                            </a>
                                                            @break
                                                        
                                                        @case('file')
                                                            <a href="{{ asset('storage/' . $fieldValue) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i> تحميل الملف
                                                            </a>
                                                            @break
                                                        
                                                        @case('image')
                                                            @if(in_array(pathinfo($fieldValue, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                                <div class="text-center">
                                                                    <img src="{{ asset('storage/' . $fieldValue) }}" 
                                                                         alt="{{ $field->label }}" 
                                                                         class="img-fluid rounded" 
                                                                         style="max-width: 300px; max-height: 200px;"
                                                                         data-bs-toggle="modal" 
                                                                         data-bs-target="#imageModal_{{ $field->id }}"
                                                                         style="cursor: pointer;">
                                                                </div>
                                                                
                                                                <!-- Modal للصورة -->
                                                                <div class="modal fade" id="imageModal_{{ $field->id }}" tabindex="-1">
                                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">{{ $field->label }}</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                            </div>
                                                                            <div class="modal-body text-center">
                                                                                <img src="{{ asset('storage/' . $fieldValue) }}" 
                                                                                     alt="{{ $field->label }}" 
                                                                                     class="img-fluid">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <a href="{{ asset('storage/' . $fieldValue) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-image"></i> عرض الصورة
                                                                </a>
                                                            @endif
                                                            @break
                                                        
                                                        @case('boolean')
                                                            @if($fieldValue)
                                                                <span class="badge bg-success"><i class="fas fa-check"></i> نعم</span>
                                                            @else
                                                                <span class="badge bg-secondary"><i class="fas fa-times"></i> لا</span>
                                                            @endif
                                                            @break
                                                        
                                                        @case('date')
                                                            <span class="text-dark">
                                                                <i class="fas fa-calendar"></i> 
                                                                {{ \Carbon\Carbon::parse($fieldValue)->format('Y-m-d') }}
                                                            </span>
                                                            @break
                                                        
                                                        @case('datetime')
                                                            <span class="text-dark">
                                                                <i class="fas fa-clock"></i> 
                                                                {{ \Carbon\Carbon::parse($fieldValue)->format('Y-m-d H:i') }}
                                                            </span>
                                                            @break
                                                        
                                                        @case('checkbox')
                                                            @php
                                                                $values = is_array($fieldValue) ? $fieldValue : explode(',', $fieldValue);
                                                            @endphp
                                                            @foreach($values as $value)
                                                                <span class="badge bg-info me-1">{{ trim($value) }}</span>
                                                            @endforeach
                                                            @break
                                                        
                                                        @default
                                                            <span class="text-dark">{{ $fieldValue }}</span>
                                                    @endswitch
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        
                        @if($section->customFields->filter(function($field) use ($sectionData) {
                            $value = $sectionData->getFieldValue($field->id);
                            return $value === null || $value === '';
                        })->count() > 0)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>ملاحظة:</strong> بعض الحقول المخصصة فارغة ولم يتم عرضها.
                            </div>
                        @endif
                    @else
                        <hr>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            لا توجد حقول مخصصة لهذا القسم.
                        </div>
                    @endif
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('admin.section-data.edit', [$section, $sectionData]) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> تحرير
                            </a>
                            <a href="{{ route('admin.section-data.index', $section) }}" class="btn btn-secondary">
                                <i class="fas fa-list"></i> العودة للقائمة
                            </a>
                        </div>
                        <div>
                            <form action="{{ route('admin.section-data.destroy', [$section, $sectionData]) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه البيانات؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // تحسين عرض الصور
    $('img[data-bs-toggle="modal"]').on('click', function() {
        $(this).addClass('shadow-lg');
    });
    
    // إزالة التأثير عند إغلاق المودال
    $('.modal').on('hidden.bs.modal', function() {
        $('img[data-bs-toggle="modal"]').removeClass('shadow-lg');
    });
});
</script>
@endsection