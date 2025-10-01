@extends('admin.layouts.app')

@section('title', 'رفع ملفات جديدة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">رفع ملفات جديدة</h3>
                </div>
                
                <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="files">اختر الملفات <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('files.*') is-invalid @enderror" 
                                       id="files" name="files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx,.txt">
                                <label class="custom-file-label" for="files">اختر الملفات...</label>
                            </div>
                            <small class="form-text text-muted">
                                يمكنك اختيار عدة ملفات في نفس الوقت. الحد الأقصى لحجم الملف الواحد: 10 ميجابايت.
                                الأنواع المدعومة: الصور، الفيديوهات، PDF، Word، النصوص.
                            </small>
                            @error('files.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- File Preview Area -->
                        <div id="filePreview" class="row" style="display: none;"></div>
                        
                        <!-- Progress Bar -->
                        <div class="progress" id="uploadProgress" style="display: none;">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                            <i class="fas fa-upload"></i> رفع الملفات
                        </button>
                        <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للمكتبة
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let selectedFiles = [];
    
    // File input change handler
    $('#files').change(function() {
        const files = Array.from(this.files);
        selectedFiles = files;
        
        if (files.length > 0) {
            $('#uploadBtn').prop('disabled', false);
            displayFilePreview(files);
            updateFileLabel(files);
        } else {
            $('#uploadBtn').prop('disabled', true);
            $('#filePreview').hide().empty();
        }
    });
    
    function updateFileLabel(files) {
        const label = files.length === 1 ? files[0].name : `تم اختيار ${files.length} ملف`;
        $('.custom-file-label').text(label);
    }
    
    function displayFilePreview(files) {
        const previewContainer = $('#filePreview');
        previewContainer.empty().show();
        
        files.forEach((file, index) => {
            const fileSize = formatFileSize(file.size);
            const isImage = file.type.startsWith('image/');
            
            let previewContent = '';
            if (isImage) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $(`#preview-${index} .file-preview`).html(
                        `<img src="${e.target.result}" class="img-fluid" style="max-height: 100px; object-fit: cover;">`
                    );
                };
                reader.readAsDataURL(file);
                previewContent = '<div class="file-preview">جاري التحميل...</div>';
            } else {
                const icon = getFileIcon(file.type);
                previewContent = `<div class="file-preview text-center"><i class="${icon} fa-3x text-muted"></i></div>`;
            }
            
            const fileCard = `
                <div class="col-md-3 mb-3" id="preview-${index}">
                    <div class="card">
                        <div class="card-body text-center p-2">
                            ${previewContent}
                            <h6 class="mt-2 mb-1">${file.name}</h6>
                            <small class="text-muted">${fileSize}</small>
                            <div class="mt-2">
                                <input type="text" class="form-control form-control-sm mb-1" 
                                       name="alt_text[${index}]" placeholder="النص البديل (للصور)">
                                <textarea class="form-control form-control-sm" 
                                          name="description[${index}]" rows="2" placeholder="الوصف (اختياري)"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            previewContainer.append(fileCard);
        });
    }
    
    function formatFileSize(bytes) {
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;
        
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        
        return Math.round(size * 100) / 100 + ' ' + units[unitIndex];
    }
    
    function getFileIcon(mimeType) {
        if (mimeType.startsWith('image/')) return 'fas fa-image';
        if (mimeType.startsWith('video/')) return 'fas fa-video';
        if (mimeType === 'application/pdf') return 'fas fa-file-pdf';
        if (mimeType.includes('word')) return 'fas fa-file-word';
        if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fas fa-file-excel';
        if (mimeType === 'text/plain') return 'fas fa-file-alt';
        return 'fas fa-file';
    }
    
    // Form submission with progress
    $('#uploadForm').submit(function(e) {
        if (selectedFiles.length === 0) {
            e.preventDefault();
            alert('يرجى اختيار ملف واحد على الأقل');
            return;
        }
        
        $('#uploadBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الرفع...');
        $('#uploadProgress').show();
        
        // Simulate progress (in real implementation, you'd use XMLHttpRequest)
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress >= 90) {
                clearInterval(progressInterval);
                progress = 90;
            }
            $('.progress-bar').css('width', progress + '%');
        }, 200);
    });
});
</script>
@endpush
@endsection