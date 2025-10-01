@extends('admin.layouts.app')

@section('title', 'إدارة الوسائط')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">مكتبة الوسائط</h3>
                    <a href="{{ route('admin.media.create') }}" class="btn btn-primary">
                        <i class="fas fa-upload"></i> رفع ملفات جديدة
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.media.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="البحث في اسم الملف أو الوصف..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="type" class="form-control">
                                    <option value="">جميع أنواع الملفات</option>
                                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>الصور</option>
                                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>الفيديوهات</option>
                                    <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>المستندات</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                            </div>
                        </div>
                    </form>

                    @if($media->count() > 0)
                        <!-- Bulk Actions -->
                        <form id="bulkForm" method="POST" action="{{ route('admin.media.index') }}" class="mb-3">
                            @csrf
                            @method('DELETE')
                            <div class="d-flex align-items-center">
                                <input type="checkbox" id="selectAll" class="mr-2">
                                <label for="selectAll" class="mr-3">تحديد الكل</label>
                                <button type="submit" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('هل أنت متأكد من حذف الملفات المحددة؟')" 
                                        disabled id="bulkDeleteBtn">
                                    <i class="fas fa-trash"></i> حذف المحدد
                                </button>
                            </div>
                        </form>

                        <!-- Media Grid -->
                        <div class="row">
                            @foreach($media as $item)
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header p-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <input type="checkbox" name="media_ids[]" value="{{ $item->id }}" 
                                                       class="media-checkbox" form="bulkForm">
                                                <small class="text-muted">{{ $item->formatted_size }}</small>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body p-2 text-center">
                                            @if($item->is_image)
                                                <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" 
                                                     class="img-fluid" style="max-height: 150px; object-fit: cover;">
                                            @else
                                                <div class="d-flex flex-column align-items-center justify-content-center" 
                                                     style="height: 150px; background: #f8f9fa;">
                                                    <i class="{{ $item->file_icon }} fa-3x text-muted mb-2"></i>
                                                    <small class="text-muted">{{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }}</small>
                                                </div>
                                            @endif
                                            
                                            <h6 class="mt-2 mb-1">{{ Str::limit($item->name, 20) }}</h6>
                                            <small class="text-muted">{{ $item->created_at->format('Y-m-d') }}</small>
                                        </div>
                                        
                                        <div class="card-footer p-2">
                                            <div class="btn-group w-100" role="group">
                                                <a href="{{ route('admin.media.show', $item) }}" 
                                                   class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.media.edit', $item) }}" 
                                                   class="btn btn-sm btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ $item->url }}" target="_blank" 
                                                   class="btn btn-sm btn-success" title="تحميل">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.media.destroy', $item) }}" 
                                                      style="display: inline;" 
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الملف؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $media->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد ملفات</h5>
                            <p class="text-muted">لم يتم العثور على أي ملفات بناءً على معايير البحث المحددة.</p>
                            <a href="{{ route('admin.media.create') }}" class="btn btn-primary">
                                <i class="fas fa-upload"></i> رفع أول ملف
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Select All functionality
    $('#selectAll').change(function() {
        $('.media-checkbox').prop('checked', this.checked);
        toggleBulkDeleteBtn();
    });
    
    // Individual checkbox change
    $('.media-checkbox').change(function() {
        toggleBulkDeleteBtn();
        
        // Update select all checkbox
        const totalCheckboxes = $('.media-checkbox').length;
        const checkedCheckboxes = $('.media-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    function toggleBulkDeleteBtn() {
        const checkedCount = $('.media-checkbox:checked').length;
        $('#bulkDeleteBtn').prop('disabled', checkedCount === 0);
    }
});
</script>
@endpush
@endsection