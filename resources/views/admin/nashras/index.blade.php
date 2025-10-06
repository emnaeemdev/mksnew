@extends('admin.layouts.app')

@section('title', 'النشرة الشهرية')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">النشرة الشهرية</h3>
                    <a href="{{ route('admin.nashras.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة نشرة جديدة
                    </a>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">الصورة المميزة</th>
                                    <th width="25%">العنوان</th>
                                    <th width="15%">العنوان الفرعي</th>
                                    <th width="10%">تاريخ النشر</th>
                                    <th width="10%">الحالة</th>
                                    <th width="10%">ترتيب العرض</th>
                                    <th width="10%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nashras as $nashra)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if($nashra->featured_image)
                                                <img src="{{ $nashra->featured_image_url }}" 
                                                     alt="{{ $nashra->title_ar }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 80px; height: 60px; object-fit: cover;">
                                            @else
                                                <span class="text-muted">لا توجد صورة</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $nashra->title_ar }}</strong>
                                        </td>
                                        <td>{{ Str::limit($nashra->subtitle_ar, 50) }}</td>
                                        <td>
                                            @if($nashra->published_at)
                                                {{ $nashra->formatted_published_date }}
                                            @else
                                                <span class="text-muted">غير محدد</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.nashras.toggle-status', $nashra->id) }}" 
                                                  method="POST" 
                                                  style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $nashra->status ? 'btn-success' : 'btn-secondary' }}">
                                                    {{ $nashra->status ? 'منشور' : 'مسودة' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $nashra->sort_order }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
  
                                                <a href="{{ route('admin.nashras.edit', $nashra->id) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.nashras.show', $nashra->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('admin.nashras.destroy', $nashra->id) }}" 
                                                      method="POST" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه النشرة؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-newspaper fa-3x mb-3"></i>
                                            <p>لا توجد نشرات حتى الآن</p>
                                            <a href="{{ route('admin.nashras.create') }}" class="btn btn-primary">
                                                إضافة أول نشرة
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($nashras->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $nashras->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // تأكيد الحذف
    function confirmDelete(id) {
        if (confirm('هل أنت متأكد من حذف هذه النشرة؟')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
    
    // إخفاء الرسائل تلقائياً
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@endpush