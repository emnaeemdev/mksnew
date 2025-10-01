@extends('admin.layouts.app')

@section('title', 'تعديل الملف')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تعديل الملف: {{ $medium->name }}</h3>
                </div>
                
                <form method="POST" action="{{ route('admin.media.update', $medium) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- File Preview -->
                                <div class="form-group">
                                    <label>معاينة الملف</label>
                                    <div class="border rounded p-3 text-center" style="background: #f8f9fa;">
                                        @if($medium->is_image)
                                            <img src="{{ $medium->url }}" alt="{{ $medium->alt_text }}" 
                                                 class="img-fluid" style="max-height: 200px; object-fit: contain;">
                                        @else
                                            <div class="d-flex flex-column align-items-center justify-content-center" style="height: 200px;">
                                                <i class="{{ $medium->file_icon }} fa-4x text-muted mb-3"></i>
                                                <h5>{{ $medium->file_name }}</h5>
                                                <small class="text-muted">{{ strtoupper(pathinfo($medium->file_name, PATHINFO_EXTENSION)) }} - {{ $medium->formatted_size }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- File Info -->
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>اسم الملف الأصلي</label>
                                            <input type="text" class="form-control" value="{{ $medium->file_name }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>حجم الملف</label>
                                            <input type="text" class="form-control" value="{{ $medium->formatted_size }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>نوع الملف</label>
                                            <input type="text" class="form-control" value="{{ $medium->mime_type }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>تاريخ الرفع</label>
                                            <input type="text" class="form-control" value="{{ $medium->created_at->format('Y-m-d H:i:s') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Editable Fields -->
                                <div class="form-group">
                                    <label for="name">اسم الملف <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $medium->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                @if($medium->is_image)
                                    <div class="form-group">
                                        <label for="alt_text">النص البديل</label>
                                        <input type="text" class="form-control @error('alt_text') is-invalid @enderror" 
                                               id="alt_text" name="alt_text" value="{{ old('alt_text', $medium->alt_text) }}" 
                                               placeholder="وصف مختصر للصورة لمحركات البحث">
                                        <small class="form-text text-muted">يساعد في تحسين SEO وإمكانية الوصول</small>
                                        @error('alt_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                                
                                <div class="form-group">
                                    <label for="description">الوصف</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" 
                                              placeholder="وصف تفصيلي للملف (اختياري)">{{ old('description', $medium->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- File URL for copying -->
                                <div class="form-group">
                                    <label>رابط الملف</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="fileUrl" value="{{ $medium->url }}" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('#fileUrl')">
                                                <i class="fas fa-copy"></i> نسخ
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">يمكنك نسخ هذا الرابط لاستخدامه في المقالات</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التغييرات
                        </button>
                        <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للمكتبة
                        </a>
                        <a href="{{ route('admin.media.show', $medium) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> عرض التفاصيل
                        </a>
                        <a href="{{ $medium->url }}" target="_blank" class="btn btn-success">
                            <i class="fas fa-download"></i> تحميل الملف
                        </a>
                    </div>
                </form>
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
    button.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
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
@endsection