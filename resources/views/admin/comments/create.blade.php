@extends('admin.layouts.app')

@section('title', 'إضافة تعليق جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">إضافة تعليق جديد</h3>
                </div>
                
                <form method="POST" action="{{ route('admin.comments.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">اسم المعلق <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
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
                                            <option value="{{ $post->id }}" {{ old('post_id') == $post->id ? 'selected' : '' }}>
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
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>في الانتظار</option>
                                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
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
                                      id="content" name="content" rows="5" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التعليق
                        </button>
                        <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للقائمة
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection