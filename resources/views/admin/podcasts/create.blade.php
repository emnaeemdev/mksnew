@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-3">
    <h4 class="mb-3">إضافة حلقة بودكاست</h4>

    <form action="{{ route('admin.podcasts.store') }}" method="post" enctype="multipart/form-data" class="row g-3">
        @csrf
        <div class="col-md-8">
            <div class="mb-3">
                <label class="form-label">العنوان</label>
                <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Slug (اختياري)</label>
                <input type="text" name="slug" value="{{ old('slug') }}" class="form-control" placeholder="مثال: court-podcast-1">
            </div>
            <div class="mb-3">
                <label class="form-label">المحتوى</label>
                <textarea name="content" class="form-control" rows="8">{{ old('content') }}</textarea>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">صورة الغلاف</label>
                <input type="file" name="cover_image" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">ملف الصوت</label>
                <input type="file" name="audio_file" class="form-control" accept="audio/*">
            </div>
            <div class="mb-3">
                <label class="form-label">Spotify URL</label>
                <input type="url" name="spotify_url" value="{{ old('spotify_url') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Apple Podcasts URL</label>
                <input type="url" name="apple_podcasts_url" value="{{ old('apple_podcasts_url') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">SoundCloud URL</label>
                <input type="url" name="soundcloud_url" value="{{ old('soundcloud_url') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">YouTube URL</label>
                <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">تاريخ النشر</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at') }}" class="form-control">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" checked>
                <label class="form-check-label" for="is_published">منشور</label>
            </div>
            <div class="mb-3">
                <label class="form-label">الترتيب</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-control" min="0">
            </div>
            <button class="btn btn-primary">حفظ</button>
            <a href="{{ route('admin.podcasts.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    if (window.tinymce) {
      tinymce.init({
        selector: 'textarea[name=content]',
        menubar: false,
        directionality: '{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}',
        plugins: 'link lists code table image media autoresize',
        toolbar: 'undo redo | styles | bold italic underline | align | bullist numlist | link | image media | removeformat | code',
        height: 400,
        relative_urls: false,
        convert_urls: false,
      });
    }
  });
</script>
@endpush