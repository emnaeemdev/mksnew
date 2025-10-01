@extends('admin.layouts.app')

@section('title', 'تعديل التعليق')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تعديل التعليق #{{ $comment->id }}</h3>
                </div>
                
                <form method="POST" action="{{ route('admin.comments.update', $comment) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">اسم المعلق <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $comment->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $comment->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="post_id">المقال <span class="text-danger">*</span></label>
                                    <select class="form-control @error('post_id') is-invalid @enderror" 
                                            id="post_id" name="post_id" required>
                                        <option value="">اختر المقال</option>
                                        @foreach($posts as $post)
                                            <option value="{{ $post->id }}" 
                                                {{ old('post_id', $comment->post_id) == $post->id ? 'selected' : '' }}>
                                                {{ $post->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('post_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">الحالة <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="pending" 
                                            {{ old('status', $comment->status) == 'pending' ? 'selected' : '' }}>في الانتظار</option>
                                        <option value="approved" 
                                            {{ old('status', $comment->status) == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                        <option value="rejected" 
                                            {{ old('status', $comment->status) == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">محتوى التعليق <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="5" required>{{ old('content', $comment->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>عنوان IP</label>
                                    <input type="text" class="form-control" value="{{ $comment->ip_address }}" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>تاريخ الإنشاء</label>
                                    <input type="text" class="form-control" value="{{ $comment->created_at->format('Y-m-d H:i:s') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التغييرات
                        </button>
                        <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للقائمة
                        </a>
                        <a href="{{ route('admin.comments.show', $comment) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> عرض التعليق
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection