@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-3">
    <h4 class="mb-3">إضافة بودكاست</h4>
    <p class="text-muted mb-3">أضف حلقة بودكاست واحدة. لإضافة سلسلة حلقات استخدم صفحة التعديل بعد الحفظ.</p>

    <form action="{{ route('admin.podcasts.store') }}" method="post" enctype="multipart/form-data" id="podcastForm">
        @csrf

        <div class="card mb-3">
            <div class="card-header">بيانات البودكاست</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">عنوان البودكاست <span class="text-danger">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                </div>
                @include('admin.partials.keyword-picker', [
                    'keywordScope' => 'podcast',
                    'selectedKeywords' => old('keywords', []),
                ])
                <div class="mb-3">
                    <label class="form-label">المحتوى / الوصف</label>
                    <textarea name="content" class="form-control" rows="8">{{ old('content') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">الملف الصوتي والغلاف</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ملف الصوت <span class="text-danger">*</span></label>
                        <input type="file" name="tracks[0][audio]" class="form-control" accept="audio/*" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">صورة الغلاف</label>
                        <input type="file" name="cover_image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">روابط المنصات والنشر</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Spotify</label>
                        <input type="url" name="spotify_url" value="{{ old('spotify_url') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apple Podcasts</label>
                        <input type="url" name="apple_podcasts_url" value="{{ old('apple_podcasts_url') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SoundCloud</label>
                        <input type="url" name="soundcloud_url" value="{{ old('soundcloud_url') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">YouTube</label>
                        <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">تاريخ النشر</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الترتيب</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-control" min="0">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" @checked(old('is_published', true))>
                            <label class="form-check-label" for="is_published">منشور</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="admin-form-actions">
                    <div class="admin-form-actions__secondary">
                        <a href="{{ route('admin.podcasts.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                    <div class="admin-form-actions__primary">
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('dashboard/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('js/admin-tinymce.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof initAdminTinyMCE === 'function') {
        initAdminTinyMCE('textarea[name=content]', { directionality: 'rtl' });
    }
});
</script>
@endpush
