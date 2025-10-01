@extends('admin.layouts.app')

@section('title', 'عرض النشرة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">عرض النشرة: {{ $nashra->title_ar }}</h3>
                    <div>
                        <a href="{{ route('admin.nashras.edit', $nashra->id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('admin.nashras.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- المعلومات الأساسية -->
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">المعلومات الأساسية</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>العنوان العربي:</strong></div>
                                        <div class="col-sm-9">{{ $nashra->title_ar }}</div>
                                    </div>
                                    
                                    @if($nashra->subtitle_ar)
                                        <div class="row mb-3">
                                            <div class="col-sm-3"><strong>العنوان الفرعي:</strong></div>
                                            <div class="col-sm-9">{{ $nashra->subtitle_ar }}</div>
                                        </div>
                                    @endif
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>الحالة:</strong></div>
                                        <div class="col-sm-9">
                                            <span class="badge {{ $nashra->status ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $nashra->status ? 'منشور' : 'مسودة' }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>ترتيب العرض:</strong></div>
                                        <div class="col-sm-9">{{ $nashra->sort_order }}</div>
                                    </div>
                                    
                                    @if($nashra->published_at)
                                        <div class="row mb-3">
                                            <div class="col-sm-3"><strong>تاريخ النشر:</strong></div>
                                            <div class="col-sm-9">{{ $nashra->formatted_published_date }}</div>
                                        </div>
                                    @endif
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>تاريخ الإنشاء:</strong></div>
                                        <div class="col-sm-9">{{ $nashra->created_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>آخر تحديث:</strong></div>
                                        <div class="col-sm-9">{{ $nashra->updated_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- المحتوى -->
                            @if($nashra->content_ar)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">المحتوى</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="content-display">
                                            {!! nl2br(e($nashra->content_ar)) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Google Sheets -->
                            @if($nashra->google_drive_url)
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">Google Sheets</h5>
                                        <button type="button" class="btn btn-sm btn-info" id="refresh-sheets-btn">
                                            <i class="fas fa-sync"></i> تحديث البيانات
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-3"><strong>رابط Google Drive:</strong></div>
                                            <div class="col-sm-9">
                                                <a href="{{ $nashra->google_drive_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt"></i> فتح الرابط
                                                </a>
                                            </div>
                                        </div>
                                        
                                        @if($nashra->google_sheet_id)
                                            <div class="row mb-3">
                                                <div class="col-sm-3"><strong>معرف الجدول:</strong></div>
                                                <div class="col-sm-9"><code>{{ $nashra->google_sheet_id }}</code></div>
                                            </div>
                                        @endif
                                        
                                        <div id="sheets-data-container">
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-table fa-2x mb-2"></i>
                                                <p>انقر على "تحديث البيانات" لعرض محتوى Google Sheets</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- الملفات والصور -->
                        <div class="col-md-4">
                            <!-- الصورة المميزة -->
                            @if($nashra->featured_image)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">الصورة المميزة</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="{{ $nashra->featured_image_url }}" 
                                             alt="{{ $nashra->title_ar }}" 
                                             class="img-fluid rounded" 
                                             style="max-height: 300px;">
                                        <div class="mt-2">
                                            <a href="{{ $nashra->featured_image_url }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt"></i> عرض بالحجم الكامل
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- ملف PDF -->
                            @if($nashra->pdf_file)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">ملف PDF</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                        </div>
                                        <p class="mb-2"><strong>{{ basename($nashra->pdf_file) }}</strong></p>
                                        <div class="d-grid gap-2">
                                            <a href="{{ $nashra->pdf_file_url }}" 
                                               target="_blank" 
                                               class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i> عرض الملف
                                            </a>
                                            <a href="{{ route('frontend.nashras.download-pdf', [app()->getLocale(), $nashra->id]) }}" 
                                               class="btn btn-outline-success">
                                                <i class="fas fa-download"></i> تحميل الملف
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- إجراءات سريعة -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">إجراءات سريعة</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <form action="{{ route('admin.nashras.toggle-status', $nashra->id) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn {{ $nashra->status === 'published' ? 'btn-warning' : 'btn-success' }} w-100">
                                                <i class="fas {{ $nashra->status === 'published' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                {{ $nashra->status === 'published' ? 'إخفاء النشرة' : 'نشر النشرة' }}
                                            </button>
                                        </form>
                                        
                                        <a href="{{ route('admin.nashras.edit', $nashra->id) }}" 
                                           class="btn btn-warning w-100">
                                            <i class="fas fa-edit"></i> تعديل النشرة
                                        </a>
                                        
                                        @if($nashra->status === 'published')
                                            <a href="{{ route('frontend.nashras.show', $nashra->id) }}" 
                                               target="_blank" 
                                               class="btn btn-info w-100">
                                                <i class="fas fa-external-link-alt"></i> عرض في الموقع
                                            </a>
                                        @endif
                                        
                                        <form action="{{ route('admin.nashras.destroy', $nashra->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذه النشرة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="fas fa-trash"></i> حذف النشرة
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // تحديث بيانات Google Sheets
    $('#refresh-sheets-btn').on('click', function() {
        const btn = $(this);
        const container = $('#sheets-data-container');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...');
        container.html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">جاري جلب البيانات...</p></div>');
        
        $.ajax({
            url: '{{ route("frontend.nashras.get-sheet-data", $nashra->id) }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    let html = '';
                    
                    if (Object.keys(response.data).length > 0) {
                        html += '<div class="accordion" id="sheetsAccordion">';
                        
                        Object.keys(response.data).forEach((sheetName, index) => {
                            const sheetData = response.data[sheetName];
                            html += `
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading${index}">
                                        <button class="accordion-button ${index === 0 ? '' : 'collapsed'}" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse${index}">
                                            ${sheetName} (${sheetData.length} صف)
                                        </button>
                                    </h2>
                                    <div id="collapse${index}" 
                                         class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" 
                                         data-bs-parent="#sheetsAccordion">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered table-striped">`;
                            
                            // عرض جميع الصفوف
                            sheetData.forEach(row => {
                                html += '<tr>';
                                if (row) {
                                    row.forEach(cell => {
                                        html += `<td>${cell || ''}</td>`;
                                    });
                                }
                                html += '</tr>';
                            });
                            
                            html += `
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        });
                        
                        html += '</div>';
                    } else {
                        html = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> لا توجد بيانات في الجدول</div>';
                    }
                    
                    container.html(html);
                } else {
                    container.html(`<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${response.message || 'حدث خطأ في جلب البيانات'}</div>`);
                }
            },
            error: function(xhr) {
                let message = 'حدث خطأ في الاتصال';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                container.html(`<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${message}</div>`);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i> تحديث البيانات');
            }
        });
    });
});
</script>
@endpush