@extends('admin.layouts.app')

@section('title', 'عرض التعليق')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تفاصيل التعليق #{{ $comment->id }}</h3>
                    <span class="badge badge-{{ $comment->status_badge }} badge-lg">
                        {{ $comment->status_text }}
                    </span>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-user"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">اسم المعلق</span>
                                    <span class="info-box-number">{{ $comment->name }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">البريد الإلكتروني</span>
                                    <span class="info-box-number">{{ $comment->email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">تاريخ الإنشاء</span>
                                    <span class="info-box-number">{{ $comment->created_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-globe"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">عنوان IP</span>
                                    <span class="info-box-number">{{ $comment->ip_address ?? 'غير محدد' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($comment->post)
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-newspaper"></i> المقال المرتبط
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <h5>{{ $comment->post->title }}</h5>
                                        <p class="text-muted">{{ Str::limit($comment->post->content ?? 'لا يوجد محتوى', 200) }}</p>
                                        <small class="text-muted">
                                            تاريخ النشر: {{ $comment->post->created_at->format('Y-m-d') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            المقال المرتبط بهذا التعليق غير موجود أو تم حذفه.
                        </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-comment"></i> محتوى التعليق
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="comment-content" style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;">
                                        {{ $comment->content }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('admin.comments.edit', $comment) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> تعديل التعليق
                            </a>
                            <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> العودة للقائمة
                            </a>
                        </div>
                        
                        <div class="col-md-6 text-right">
                            @if($comment->status !== 'approved')
                                <form method="POST" action="{{ route('admin.comments.update', $comment) }}" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $comment->name }}">
                                    <input type="hidden" name="email" value="{{ $comment->email }}">
                                    <input type="hidden" name="content" value="{{ $comment->content }}">
                                    <input type="hidden" name="post_id" value="{{ $comment->post_id }}">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> الموافقة على التعليق
                                    </button>
                                </form>
                            @endif
                            
                            @if($comment->status !== 'rejected')
                                <form method="POST" action="{{ route('admin.comments.update', $comment) }}" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $comment->name }}">
                                    <input type="hidden" name="email" value="{{ $comment->email }}">
                                    <input type="hidden" name="content" value="{{ $comment->content }}">
                                    <input type="hidden" name="post_id" value="{{ $comment->post_id }}">
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-times"></i> رفض التعليق
                                    </button>
                                </form>
                            @endif
                            
                            <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" 
                                  style="display: inline;" 
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا التعليق؟ هذا الإجراء لا يمكن التراجع عنه.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> حذف التعليق
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