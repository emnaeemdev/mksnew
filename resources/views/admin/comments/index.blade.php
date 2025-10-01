@extends('admin.layouts.app')

@section('title', 'إدارة التعليقات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إدارة التعليقات</h3>
                    <a href="{{ route('admin.comments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة تعليق جديد
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.comments.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="البحث في الاسم، الإيميل، أو المحتوى..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>في الانتظار</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                            </div>
                        </div>
                    </form>

                    @if($comments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>الإيميل</th>
                                        <th>المقال</th>
                                        <th>المحتوى</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comments as $comment)
                                        <tr>
                                            <td>{{ $comment->id }}</td>
                                            <td>{{ $comment->name }}</td>
                                            <td>{{ $comment->email }}</td>
                                            <td>
                                                @if($comment->post)
                                                    <a href="#" class="text-primary">{{ Str::limit($comment->post->title, 30) }}</a>
                                                @else
                                                    <span class="text-muted">مقال محذوف</span>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($comment->content, 50) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $comment->status_badge }}">
                                                    {{ $comment->status_text }}
                                                </span>
                                            </td>
                                            <td>{{ $comment->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.comments.show', $comment) }}" 
                                                       class="btn btn-sm btn-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.comments.edit', $comment) }}" 
                                                       class="btn btn-sm btn-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($comment->status !== 'approved')
                                                        <form method="POST" action="{{ route('admin.comments.update', $comment) }}" style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="approved">
                                                            <button type="submit" class="btn btn-sm btn-success" title="موافقة">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($comment->status !== 'rejected')
                                                        <form method="POST" action="{{ route('admin.comments.update', $comment) }}" style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button type="submit" class="btn btn-sm btn-warning" title="رفض">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا التعليق؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $comments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد تعليقات</h5>
                            <p class="text-muted">لم يتم العثور على أي تعليقات بناءً على معايير البحث المحددة.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection