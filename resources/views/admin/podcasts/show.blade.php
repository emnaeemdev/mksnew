@extends('admin.layouts.app')

@section('title', 'عرض البودكاست')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>عرض البودكاست</h4>
        <div>
            <a href="{{ route('admin.podcasts.edit', $podcast) }}" class="btn btn-warning">تعديل</a>
            <a href="{{ route('admin.podcasts.index') }}" class="btn btn-secondary">عودة للقائمة</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-2">{{ $podcast->title }}</h5>
                    <p class="text-muted mb-2">
                        <i class="fa-regular fa-calendar"></i>
                        {{ optional($podcast->published_at)->format('Y-m-d') }}
                        <span class="mx-2">|</span>
                        <span class="badge bg-{{ $podcast->is_published ? 'success' : 'secondary' }}">{{ $podcast->is_published ? 'منشور' : 'مسودة' }}</span>
                    </p>

                    @if($podcast->content)
                        <div class="mb-3">
                            {!! $podcast->content !!}
                        </div>
                    @endif

                    @if($podcast->audio_path)
                        <audio controls class="w-100">
                            <source src="{{ asset('storage/' . $podcast->audio_path) }}" type="audio/mpeg">
                            متصفحك لا يدعم تشغيل الصوت.
                        </audio>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">الغلاف</div>
                <div class="card-body">
                    @if($podcast->cover_image_path)
                        <img src="{{ asset('storage/' . $podcast->cover_image_path) }}" class="img-fluid rounded" alt="cover">
                    @else
                        <div class="text-muted">لا يوجد غلاف</div>
                    @endif
                </div>
            </div>
            <div class="card">
                <div class="card-header">روابط المنصات</div>
                <div class="card-body">
                    <ul class="list-unstyled m-0">
                        @if($podcast->spotify_url)
                            <li class="mb-2"><i class="fa-brands fa-spotify text-success"></i> <a href="{{ $podcast->spotify_url }}" target="_blank">Spotify</a></li>
                        @endif
                        @if($podcast->apple_podcasts_url)
                            <li class="mb-2"><i class="fa-brands fa-apple text-dark"></i> <a href="{{ $podcast->apple_podcasts_url }}" target="_blank">Apple Podcasts</a></li>
                        @endif
                        @if($podcast->soundcloud_url)
                            <li class="mb-2"><i class="fa-brands fa-soundcloud text-warning"></i> <a href="{{ $podcast->soundcloud_url }}" target="_blank">SoundCloud</a></li>
                        @endif
                        @if($podcast->youtube_url)
                            <li class="mb-2"><i class="fa-brands fa-youtube text-danger"></i> <a href="{{ $podcast->youtube_url }}" target="_blank">YouTube</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection