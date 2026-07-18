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
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-2">{{ $podcast->title }}</h5>
                    <p class="text-muted mb-2">
                        <i class="fa-regular fa-calendar"></i>
                        {{ optional($podcast->published_at)->format('Y-m-d') }}
                        <span class="mx-2">|</span>
                        <span class="badge bg-{{ $podcast->is_published ? 'success' : 'secondary' }}">{{ $podcast->is_published ? 'منشور' : 'مسودة' }}</span>
                        <span class="mx-2">|</span>
                        <span class="badge bg-info">{{ $podcast->tracks->count() }} حلقة</span>
                    </p>

                    @if($podcast->content)
                        <div class="mb-3">{!! safe_html($podcast->content) !!}</div>
                    @endif
                </div>
            </div>

            @forelse($podcast->tracks as $index => $track)
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-1">{{ $podcast->tracks->count() > 1 ? (($index + 1) . '. ') : '' }}{{ $track->displayTitle($podcast->title) }}</h6>
                        @if($track->description)
                            <p class="text-muted small">{{ $track->description }}</p>
                        @endif
                        <audio controls preload="metadata" class="w-100 mb-2">
                            <source src="{{ route('admin.podcasts.tracks.stream', [$podcast, $track]) }}" type="audio/mpeg">
                        </audio>
                        @if($podcast->tracks->count() > 1)
                            @php $links = $track->socialLinks(); @endphp
                            @if(!empty($links))
                                <div class="d-flex flex-wrap gap-2 small">
                                    @foreach($links as $key => $url)
                                        <a href="{{ $url }}" target="_blank" class="badge text-bg-light text-decoration-none">{{ $key }}</a>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert alert-warning">لا توجد حلقات صوتية.</div>
            @endforelse
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
                <div class="card-header">روابط المنصات {{ $podcast->tracks->count() > 1 ? '(مستوى البودكاست)' : '' }}</div>
                <div class="card-body">
                    @if($podcast->tracks->count() > 1)
                        <p class="text-muted small">عند تعدد الحلقات، الروابط الفعّالة تكون لكل حلقة من صفحة التعديل.</p>
                    @endif
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
