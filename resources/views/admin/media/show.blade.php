@extends('admin.layouts.app')

@section('title', 'تفاصيل الملف')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تفاصيل الملف: {{ $medium->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.media.edit', $medium) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ $medium->url }}" target="_blank" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> تحميل
                        </a>
                        <form method="POST" action="{{ route('admin.media.destroy', $medium) }}" 
                              style="display: inline-block;" 
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا الملف؟ لا يمكن التراجع عن هذا الإجراء.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- File Preview -->
                            <div class="form-group">
                                <label>معاينة الملف</label>
                                <div class="border rounded p-3 text-center" style="background: #f8f9fa;">
                                    @if($medium->is_image)
                                        <img src="{{ $medium->url }}" alt="{{ $medium->alt_text }}" 
                                             class="img-fluid" style="max-height: 400px; object-fit: contain;">
                                    @elseif($medium->is_video)
                                        <video controls class="img-fluid" style="max-height: 400px;">
                                            <source src="{{ $medium->url }}" type="{{ $medium->mime_type }}">
                                            متصفحك لا يدعم تشغيل الفيديو.
                                        </video>
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center" style="height: 300px;">
                                            <i class="{{ $medium->file_icon }} fa-5x text-muted mb-3"></i>
                                            <h4>{{ $medium->file_name }}</h4>
                                            <p class="text-muted">{{ strtoupper(pathinfo($medium->file_name, PATHINFO_EXTENSION)) }} - {{ $medium->formatted_size }}</p>
                                            <a href="{{ $medium->url }}" target="_blank" class="btn btn-primary">
                                                <i class="fas fa-external-link-alt"></i> فتح الملف
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($medium->description)
                                <div class="form-group">
                                    <label>الوصف</label>
                                    <div class="border rounded p-3" style="background: #f8f9fa;">
                                        {{ $medium->description }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <!-- File Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">معلومات الملف</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>الاسم:</strong></td>
                                            <td>{{ $medium->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>اسم الملف الأصلي:</strong></td>
                                            <td>{{ $medium->file_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>حجم الملف:</strong></td>
                                            <td>{{ $medium->formatted_size }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>نوع الملف:</strong></td>
                                            <td>{{ $medium->mime_type }}</td>
                                        </tr>
                                        @if($medium->alt_text)
                                            <tr>
                                                <td><strong>النص البديل:</strong></td>
                                                <td>{{ $medium->alt_text }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td><strong>تاريخ الرفع:</strong></td>
                                            <td>{{ $medium->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>آخر تعديل:</strong></td>
                                            <td>{{ $medium->updated_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @if($medium->uploader)
                                            <tr>
                                                <td><strong>رفع بواسطة:</strong></td>
                                                <td>{{ $medium->uploader->name }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            
                            <!-- File URL -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">رابط الملف</h5>
                                </div>
                                <div class="card-body">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="fileUrl" value="{{ url('' . $medium->url ) }}" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('#fileUrl')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted mt-2">انقر على زر النسخ لنسخ الرابط</small>
                                </div>
                            </div>
                            
                            <!-- File Type Badge -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">نوع الملف</h5>
                                </div>
                                <div class="card-body text-center">
                                    @if($medium->is_image)
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-image"></i> صورة
                                        </span>
                                    @elseif($medium->is_video)
                                        <span class="badge badge-info badge-lg">
                                            <i class="fas fa-video"></i> فيديو
                                        </span>
                                    @elseif($medium->is_document)
                                        <span class="badge badge-warning badge-lg">
                                            <i class="fas fa-file-alt"></i> مستند
                                        </span>
                                    @else
                                        <span class="badge badge-secondary badge-lg">
                                            <i class="fas fa-file"></i> ملف
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> العودة للمكتبة
                    </a>
                    <a href="{{ route('admin.media.edit', $medium) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> تعديل الملف
                    </a>
                    <a href="{{ $medium->url }}" target="_blank" class="btn btn-success">
                        <i class="fas fa-download"></i> تحميل الملف
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(element) {
    const input = document.querySelector(element);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show feedback
    const button = input.nextElementSibling.querySelector('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.remove('btn-outline-secondary');
    button.classList.add('btn-success');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>
@endpush

@push('styles')
<style>
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}
</style>
@endpush
@endsection