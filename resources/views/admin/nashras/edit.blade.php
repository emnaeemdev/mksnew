@extends('admin.layouts.app')

@section('title', 'تعديل النشرة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تعديل النشرة: {{ $nashra->title_ar }}</h3>
                    <div>
                        <a href="{{ route('admin.nashras.show', $nashra->id) }}" class="btn btn-info me-2">
                            <i class="fas fa-eye"></i> عرض
                        </a>
                        <a href="{{ route('admin.nashras.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('admin.nashras.update', $nashra->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="row">
                            <!-- العنوان العربي -->
                            <div class="col-md-6 mb-3">
                                <label for="title_ar" class="form-label">العنوان العربي <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('title_ar') is-invalid @enderror" 
                                       id="title_ar" 
                                       name="title_ar" 
                                       value="{{ old('title_ar', $nashra->title_ar) }}" 
                                       required>
                                @error('title_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- العنوان الفرعي -->
                            <div class="col-md-6 mb-3">
                                <label for="subtitle_ar" class="form-label">العنوان الفرعي</label>
                                <input type="text" 
                                       class="form-control @error('subtitle_ar') is-invalid @enderror" 
                                       id="subtitle_ar" 
                                       name="subtitle_ar" 
                                       value="{{ old('subtitle_ar', $nashra->subtitle_ar) }}">
                                @error('subtitle_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- رابط Google Drive -->
                            <div class="col-md-6 mb-3">
                                <label for="google_drive_url" class="form-label">رابط Google Drive</label>
                                <input type="url" 
                                       class="form-control @error('google_drive_url') is-invalid @enderror" 
                                       id="google_drive_url" 
                                       name="google_drive_url" 
                                       value="{{ old('google_drive_url', $nashra->google_drive_url) }}"
                                       placeholder="https://docs.google.com/spreadsheets/d/...">
                                @error('google_drive_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    سيتم استخراج معرف Google Sheet تلقائياً من الرابط
                                    @if($nashra->google_sheet_id)
                                        <br><strong>المعرف الحالي:</strong> {{ $nashra->google_sheet_id }}
                                    @endif
                                </div>
                            </div>
                            
                            <!-- تاريخ النشر -->
                            <div class="col-md-6 mb-3">
                                <label for="published_at" class="form-label">تاريخ النشر</label>
                                <input type="datetime-local" 
                                       class="form-control @error('published_at') is-invalid @enderror" 
                                       id="published_at" 
                                       name="published_at" 
                                       value="{{ old('published_at', $nashra->published_at ? $nashra->published_at->format('Y-m-d\TH:i') : '') }}">
                                @error('published_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- الحالة -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">الحالة</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status">
                                    <option value="0" {{ old('status', $nashra->status ? 1 : 0) == 0 ? 'selected' : '' }}>مسودة</option>
                                    <option value="1" {{ old('status', $nashra->status ? 1 : 0) == 1 ? 'selected' : '' }}>منشور</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- ترتيب العرض -->
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">ترتيب العرض</label>
                                <input type="number" 
                                       class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" 
                                       name="sort_order" 
                                       value="{{ old('sort_order', $nashra->sort_order) }}"
                                       min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">الرقم الأصغر يظهر أولاً</div>
                            </div>
                        </div>
                        
                        <!-- المحتوى -->
                        <div class="mb-3">
                            <label for="content_ar" class="form-label">المحتوى</label>
                            <textarea class="form-control tinymce-editor @error('content_ar') is-invalid @enderror" 
                                      id="content_ar" 
                                      name="content_ar" 
                                      rows="6">{{ old('content_ar', $nashra->content_ar) }}</textarea>
                            @error('content_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <!-- الصورة المميزة -->
                            <div class="col-md-6 mb-3">
                                <label for="featured_image" class="form-label">الصورة المميزة</label>
                                @if($nashra->featured_image)
                                    <div class="mb-2">
                                        <img src="{{ $nashra->featured_image_url }}" 
                                             alt="{{ $nashra->title_ar }}" 
                                             class="img-thumbnail" 
                                             style="max-width: 200px; max-height: 150px;">
                                        <div class="form-text">الصورة الحالية</div>
                                    </div>
                                @endif
                                <input type="file" 
                                       class="form-control @error('featured_image') is-invalid @enderror" 
                                       id="featured_image" 
                                       name="featured_image" 
                                       accept="image/*">
                                @error('featured_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">الصيغ المدعومة: JPG, PNG, GIF (الحد الأقصى: 2MB) - اتركه فارغاً للاحتفاظ بالصورة الحالية</div>
                            </div>
                            
                            <!-- ملف PDF -->
                            <div class="col-md-6 mb-3">
                                <label for="pdf_file" class="form-label">ملف PDF</label>
                                @if($nashra->pdf_file)
                                    <div class="mb-2">
                                        <a href="{{ $nashra->pdf_file_url }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-pdf"></i> عرض الملف الحالي
                                        </a>
                                        <div class="form-text">الملف الحالي: {{ basename($nashra->pdf_file) }}</div>
                                    </div>
                                @endif
                                <input type="file" 
                                       class="form-control @error('pdf_file') is-invalid @enderror" 
                                       id="pdf_file" 
                                       name="pdf_file" 
                                       accept=".pdf">
                                @error('pdf_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">ملف PDF للنشرة (الحد الأقصى: 10MB) - اتركه فارغاً للاحتفاظ بالملف الحالي</div>
                            </div>
                        </div>
                        
                        <!-- معاينة Google Sheets -->
                        <div class="mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">معاينة Google Sheets</h5>
                                </div>
                                <div class="card-body">
                                    <button type="button" 
                                            class="btn btn-info" 
                                            id="fetch-sheets-btn" 
                                            {{ $nashra->google_drive_url ? '' : 'disabled' }}>
                                        <i class="fas fa-sync"></i> جلب الأوراق
                                    </button>
                                    <div id="sheets-preview" class="mt-3">
                                        @if($nashra->google_sheet_id)
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> 
                                                انقر على "جلب الأوراق" لعرض البيانات الحالية من Google Sheets
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.nashras.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ التعديلات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // تفعيل زر جلب الأوراق عند إدخال رابط Google Drive
    $('#google_drive_url').on('input', function() {
        const url = $(this).val();
        const fetchBtn = $('#fetch-sheets-btn');
        
        if (url && url.includes('docs.google.com/spreadsheets')) {
            fetchBtn.prop('disabled', false);
        } else {
            fetchBtn.prop('disabled', true);
            $('#sheets-preview').empty();
        }
    });
    
    // جلب أوراق Google Sheets
    $('#fetch-sheets-btn').on('click', function() {
        const btn = $(this);
        const url = $('#google_drive_url').val();
        const preview = $('#sheets-preview');
        
        if (!url) {
            alert('يرجى إدخال رابط Google Drive أولاً');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الجلب...');
        preview.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> جاري جلب البيانات...</div>');
        
        $.ajax({
            url: '{{ route("admin.nashras.fetch-sheets") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                google_drive_url: url
            },
            success: function(response) {
                if (response.success) {
                    let html = '<div class="alert alert-success">تم جلب البيانات بنجاح!</div>';
                    
                    if (response.sheets && Object.keys(response.sheets).length > 0) {
                        html += '<div class="accordion" id="sheetsAccordion">';
                        
                        Object.keys(response.sheets).forEach((sheetName, index) => {
                            const sheetData = response.sheets[sheetName];
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
                                                <table class="table table-sm table-bordered">`;
                            
                            // عرض أول 5 صفوف فقط
                            const rowsToShow = Math.min(5, sheetData.length);
                            for (let i = 0; i < rowsToShow; i++) {
                                html += '<tr>';
                                if (sheetData[i]) {
                                    sheetData[i].forEach(cell => {
                                        html += `<td>${cell || ''}</td>`;
                                    });
                                }
                                html += '</tr>';
                            }
                            
                            if (sheetData.length > 5) {
                                html += `<tr><td colspan="100%" class="text-center text-muted">... و ${sheetData.length - 5} صف آخر</td></tr>`;
                            }
                            
                            html += `
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        });
                        
                        html += '</div>';
                    } else {
                        html += '<div class="alert alert-warning">لا توجد بيانات في الجدول</div>';
                    }
                    
                    preview.html(html);
                } else {
                    preview.html(`<div class="alert alert-danger">${response.message || 'حدث خطأ في جلب البيانات'}</div>`);
                }
            },
            error: function(xhr) {
                let message = 'حدث خطأ في الاتصال';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                preview.html(`<div class="alert alert-danger">${message}</div>`);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i> جلب الأوراق');
            }
        });
    });

    // Initialize TinyMCE
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#content_ar',
            height: 400,
            directionality: 'rtl',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons',
                'codesample', 'nonbreaking', 'pagebreak', 'save', 'directionality'
            ],
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | ' +
                    'alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | ' +
                    'forecolor backcolor removeformat | pagebreak | charmap emoticons | ' +
                    'fullscreen preview save print | insertfile image media template link anchor codesample | ' +
                    'ltr rtl | help',
            menubar: 'file edit view insert format tools table help',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; direction: rtl; }',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
            font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Times New Roman=times new roman,times,serif; Tahoma=tahoma,arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif',
            image_advtab: true,
            link_assume_external_targets: true,
            file_picker_types: 'image',
            automatic_uploads: true,
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true
        });
    }
});
</script>

<!-- TinyMCE -->
<script src="{{ asset('dashboard/tinymce/tinymce.min.js') }}"></script>
@endpush