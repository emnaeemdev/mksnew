@extends('frontend.layouts.app')

@section('title', $title ?? (app()->isLocale('ar') ? 'بودكاست محكمة' : 'Court Podcast'))
@section('meta')
    <meta name="description" content="{{ $description ?? '' }}">
@endsection

@section('styles')
<style>
.podcast-show-shell{display:grid;gap:20px}
.podcast-hero{
    background:linear-gradient(145deg,#1f3b87 0%,#2b4596 45%,#3d5fad 100%);
    color:#fff;border-radius:22px;overflow:hidden;
    display:grid;grid-template-columns:minmax(280px,30%) 1fr;gap:0;align-items:stretch;
}
.podcast-hero-cover{width:100%;height:100%;min-height:320px;object-fit:cover;background:#16306f;display:block}
.podcast-hero-body{padding:18px 20px;display:flex;flex-direction:column;justify-content:center}
.podcast-hero-kicker{opacity:.85;font-size:.85rem;margin-bottom:6px}
.podcast-hero-title{font-size:1.35rem;font-weight:800;line-height:1.45;margin-bottom:8px}
.podcast-hero-meta{opacity:.9;margin-bottom:12px;font-size:.92rem}
.podcast-player-box{background:rgba(255,255,255,.12);border-radius:14px;padding:12px 14px}
.podcast-now-playing{font-weight:700;margin-bottom:8px;font-size:.95rem}
.podcast-controls{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:8px}
.podcast-btn{
    width:40px;height:40px;border:0;border-radius:50%;
    background:rgba(255,255,255,.18);color:#fff;display:inline-flex;
    align-items:center;justify-content:center;
}
.podcast-btn-main{width:50px;height:50px;background:#fff;color:#2b4596}
.podcast-seek-row{display:flex;align-items:center;gap:8px;font-size:.85rem}
.podcast-seek-row input{flex:1}
.podcast-extra{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:8px}
.podcast-extra .form-select{width:auto;min-width:80px}
.podcast-volume{display:inline-flex;align-items:center;gap:6px}
.podcast-social-links{margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;min-height:34px}
.podcast-social-links a{display:none}
.podcast-social-links a.is-visible{display:inline-flex;align-items:center;gap:6px}
.podcast-series{background:#fff;border:1px solid #e8edf3;border-radius:18px;padding:18px}
.podcast-series h5{color:#2b4596;font-weight:700;margin-bottom:14px}
.podcast-track-item{
    display:flex;gap:12px;align-items:center;width:100%;text-align:right;
    border:1px solid #e8edf3;border-radius:14px;padding:12px;background:#f8fafc;
    margin-bottom:10px;cursor:pointer;transition:.2s ease;
}
.podcast-track-item:hover,.podcast-track-item.active{border-color:#c6d7f4;background:#eef3fb}
.podcast-track-thumb{width:64px;height:64px;border-radius:12px;object-fit:cover;flex-shrink:0;background:#d9e3f5}
.podcast-track-num{
    width:28px;height:28px;border-radius:50%;background:#2b4596;color:#fff;
    display:inline-flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;font-size:.8rem;
}
.podcast-track-title{font-weight:700;color:#243f8f;margin-bottom:4px}
.podcast-track-desc{color:#6c757d;font-size:.9rem;line-height:1.6}
.podcast-content-card{background:#fff;border:1px solid #e8edf3;border-radius:18px;padding:22px}
@media (max-width: 768px){
    .podcast-hero{grid-template-columns:1fr}
    .podcast-hero-cover{min-height:240px;max-height:280px}
}
</style>
@endsection

@section('content')
@php
    $tracks = $podcast->tracks;
    $activeTrack = $activeTrack ?? $tracks->first();
    $isSeries = $tracks->count() > 1;
    $podcastCover = $podcast->cover_image_path;
    $heroCover = $activeTrack
        ? $activeTrack->coverUrl($podcastCover)
        : ($podcastCover ? asset('storage/'.$podcastCover) : 'https://via.placeholder.com/800x800?text=Podcast');

    $activeLinks = [];
    if ($isSeries && $activeTrack) {
        $activeLinks = $activeTrack->socialLinks();
    } else {
        $activeLinks = array_filter([
            'spotify' => $podcast->spotify_url,
            'apple' => $podcast->apple_podcasts_url,
            'soundcloud' => $podcast->soundcloud_url,
            'youtube' => $podcast->youtube_url,
        ]);
    }
@endphp
<div class="container py-4 podcast-show-shell">
    <div class="podcast-hero">
        <img class="podcast-hero-cover" id="podcastHeroCover"
             src="{{ $heroCover }}"
             alt="{{ $podcast->title }}">
        <div class="podcast-hero-body">
            <div class="podcast-hero-kicker">
                {{ $isSeries ? (app()->isLocale('ar') ? 'سلسلة حلقات' : 'Episode series') : (app()->isLocale('ar') ? 'حلقة بودكاست' : 'Podcast episode') }}
                @if($isSeries)
                    · {{ $tracks->count() }} {{ app()->isLocale('ar') ? 'حلقات' : 'tracks' }}
                @endif
            </div>
            <h1 class="podcast-hero-title">{{ $podcast->title }}</h1>
            <div class="podcast-hero-meta">
                <i class="fa-regular fa-calendar"></i>
                {{ optional($podcast->published_at)->format('Y-m-d') ?? '' }}
            </div>

            @if($activeTrack)
                <div class="podcast-player-box" id="podcast-player-app"
                     data-active-title="{{ $activeTrack->displayTitle($podcast->title) }}">
                    <div class="podcast-now-playing {{ $isSeries ? '' : 'd-none' }}" id="nowPlayingTitle" @if(!$isSeries) hidden @endif>
                        {{ $activeTrack->displayTitle($podcast->title) }}
                    </div>

                    <div class="podcast-controls">
                        <button type="button" class="podcast-btn" id="btn-back" aria-label="رجوع 10 ثوان"><i class="material-icons">replay_10</i></button>
                        <button type="button" class="podcast-btn podcast-btn-main" id="btn-play" aria-label="تشغيل"><i class="fa-solid fa-play"></i></button>
                        <button type="button" class="podcast-btn" id="btn-forward" aria-label="تقديم 10 ثوان"><i class="material-icons">forward_10</i></button>
                    </div>

                    <div class="podcast-seek-row">
                        <span id="currentTime">0:00</span>
                        <input type="range" id="seek" class="form-range" min="0" max="100" value="0">
                        <span id="duration">0:00</span>
                    </div>

                    <div class="podcast-extra">
                        <div class="podcast-volume">
                            <button type="button" class="podcast-btn" id="btn-vol-down"><i class="fa-solid fa-volume-down"></i></button>
                            <button type="button" class="podcast-btn" id="btn-vol-up"><i class="fa-solid fa-volume-up"></i></button>
                            <span id="volumePct">100%</span>
                        </div>
                        <select id="speed" class="form-select form-select-sm">
                            <option value="0.75">0.75x</option>
                            <option value="1" selected>1x</option>
                            <option value="1.25">1.25x</option>
                            <option value="1.5">1.5x</option>
                            <option value="1.75">1.75x</option>
                            <option value="2">2x</option>
                        </select>
                    </div>

                    <audio id="audio" preload="metadata"
                           src="{{ route('frontend.podcasts.tracks.stream', [app()->getLocale(), $podcast->id, $activeTrack->id]) }}"></audio>
                </div>
            @else
                <div class="alert alert-light text-dark mb-0">لا يوجد ملف صوت لهذه الحلقة حالياً.</div>
            @endif

            <div class="podcast-social-links" id="podcastSocialLinks"
                 data-mode="{{ $isSeries ? 'track' : 'podcast' }}">
                <a class="btn btn-sm btn-success {{ !empty($activeLinks['spotify']) ? 'is-visible' : '' }}"
                   data-social="spotify"
                   href="{{ $activeLinks['spotify'] ?? '#' }}"
                   target="_blank" rel="noopener"><i class="fa-brands fa-spotify"></i> Spotify</a>
                <a class="btn btn-sm btn-dark {{ !empty($activeLinks['apple']) ? 'is-visible' : '' }}"
                   data-social="apple"
                   href="{{ $activeLinks['apple'] ?? '#' }}"
                   target="_blank" rel="noopener"><i class="fa-brands fa-apple"></i> Apple</a>
                <a class="btn btn-sm btn-warning {{ !empty($activeLinks['soundcloud']) ? 'is-visible' : '' }}"
                   data-social="soundcloud"
                   href="{{ $activeLinks['soundcloud'] ?? '#' }}"
                   target="_blank" rel="noopener"><i class="fa-brands fa-soundcloud"></i> SoundCloud</a>
                <a class="btn btn-sm btn-danger {{ !empty($activeLinks['youtube']) ? 'is-visible' : '' }}"
                   data-social="youtube"
                   href="{{ $activeLinks['youtube'] ?? '#' }}"
                   target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i> YouTube</a>
            </div>
        </div>
    </div>

    @if($isSeries)
        <div class="podcast-series">
            <h5>{{ $podcast->seriesHeading() }}</h5>
            @foreach($tracks as $index => $track)
                @php $trackLinks = $track->socialLinks(); @endphp
                <button type="button"
                        class="podcast-track-item {{ $activeTrack && $activeTrack->id === $track->id ? 'active' : '' }}"
                        data-track-id="{{ $track->id }}"
                        data-track-title="{{ $track->displayTitle($podcast->title) }}"
                        data-track-src="{{ route('frontend.podcasts.tracks.stream', [app()->getLocale(), $podcast->id, $track->id]) }}"
                        data-track-cover="{{ $track->coverUrl($podcastCover) }}"
                        data-spotify="{{ $trackLinks['spotify'] ?? '' }}"
                        data-apple="{{ $trackLinks['apple'] ?? '' }}"
                        data-soundcloud="{{ $trackLinks['soundcloud'] ?? '' }}"
                        data-youtube="{{ $trackLinks['youtube'] ?? '' }}">
                    <img class="podcast-track-thumb" src="{{ $track->coverUrl($podcastCover) }}" alt="">
                    <span class="podcast-track-num">{{ $index + 1 }}</span>
                    <span class="flex-grow-1">
                        <div class="podcast-track-title">{{ $track->displayTitle($podcast->title) }}</div>
                        @if($track->description)
                            <div class="podcast-track-desc">{{ $track->description }}</div>
                        @endif
                    </span>
                </button>
            @endforeach
        </div>
    @elseif($activeTrack && $activeTrack->description)
        <div class="podcast-content-card">
            <h5 class="mb-3">{{ app()->isLocale('ar') ? 'عن الحلقة' : 'About this episode' }}</h5>
            <div>{{ $activeTrack->description }}</div>
        </div>
    @endif

    @if($podcast->content)
        <div class="podcast-content-card">
            {!! $podcast->content !!}
        </div>
    @endif

    <div class="podcast-series">
        <h5>{{ app()->isLocale('ar') ? 'بودكاست أخرى' : 'More podcasts' }}</h5>
        <div class="row g-3">
            @foreach(\App\Models\Podcast::where('is_published', true)->where('id', '!=', $podcast->id)->latest('published_at')->take(6)->get() as $item)
                <div class="col-md-6 col-lg-4">
                    <a class="text-decoration-none" href="{{ route('frontend.podcasts.show', [app()->getLocale(), $item->id]) }}">
                        <div class="d-flex align-items-center gap-2 p-2 border rounded">
                            <img src="{{ $item->cover_image_path ? asset('storage/'.$item->cover_image_path) : 'https://via.placeholder.com/80x80?text=P' }}"
                                 alt="{{ $item->title }}" style="width:64px;height:64px;object-fit:cover;border-radius:10px">
                            <div>
                                <div class="fw-bold text-dark">{{ $item->title }}</div>
                                <small class="text-muted">{{ optional($item->published_at)->format('Y-m-d') }}</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
@endsection

@push('scripts')
<script>
(function(){
    const audio = document.getElementById('audio');
    if (!audio) return;

    const btnPlay = document.getElementById('btn-play');
    const btnBack = document.getElementById('btn-back');
    const btnForward = document.getElementById('btn-forward');
    const seek = document.getElementById('seek');
    const currentTimeEl = document.getElementById('currentTime');
    const durationEl = document.getElementById('duration');
    const speed = document.getElementById('speed');
    const btnVolUp = document.getElementById('btn-vol-up');
    const btnVolDown = document.getElementById('btn-vol-down');
    const volumePct = document.getElementById('volumePct');
    const nowPlayingTitle = document.getElementById('nowPlayingTitle');
    const heroCover = document.getElementById('podcastHeroCover');
    const socialBox = document.getElementById('podcastSocialLinks');
    let seeking = false;

    const format = (s)=>{
        s = Math.floor(s||0);
        const m = Math.floor(s/60);
        const ss = (s%60).toString().padStart(2,'0');
        return m + ':' + ss;
    };

    function updateSeek(){
        if(!audio.duration || seeking) return;
        seek.value = (audio.currentTime / audio.duration) * 100 || 0;
        currentTimeEl.textContent = format(audio.currentTime);
        durationEl.textContent = isFinite(audio.duration) ? format(audio.duration) : '0:00';
    }

    function setVolume(v){
        v = Math.max(0, Math.min(1, parseFloat(v||0)));
        audio.volume = v;
        volumePct.textContent = Math.round(v*100) + '%';
    }

    function setPlayingUi(isPlaying){
        btnPlay.innerHTML = isPlaying ? '<i class="fa-solid fa-pause"></i>' : '<i class="fa-solid fa-play"></i>';
    }

    function updateSocialLinks(map){
        if (!socialBox) return;
        ['spotify','apple','soundcloud','youtube'].forEach(function(key){
            const link = socialBox.querySelector('[data-social="'+key+'"]');
            if (!link) return;
            const url = (map[key] || '').trim();
            if (url) {
                link.href = url;
                link.classList.add('is-visible');
            } else {
                link.href = '#';
                link.classList.remove('is-visible');
            }
        });
    }

    setVolume(1);

    btnPlay.addEventListener('click', ()=>{
        if(audio.paused){ audio.play(); setPlayingUi(true); }
        else { audio.pause(); setPlayingUi(false); }
    });
    btnBack.addEventListener('click', ()=>{ audio.currentTime = Math.max(0, audio.currentTime - 10); });
    btnForward.addEventListener('click', ()=>{ audio.currentTime = Math.min(audio.duration||0, audio.currentTime + 10); });
    speed.addEventListener('change', ()=>{ audio.playbackRate = parseFloat(speed.value); });
    btnVolUp.addEventListener('click', ()=> setVolume(audio.volume + 0.1));
    btnVolDown.addEventListener('click', ()=> setVolume(audio.volume - 0.1));
    seek.addEventListener('input', ()=>{ seeking = true; });
    seek.addEventListener('change', ()=>{ if(audio.duration){ audio.currentTime = (seek.value/100) * audio.duration; } seeking = false; });
    audio.addEventListener('timeupdate', updateSeek);
    audio.addEventListener('loadedmetadata', updateSeek);
    audio.addEventListener('play', ()=> setPlayingUi(true));
    audio.addEventListener('pause', ()=> setPlayingUi(false));

    document.querySelectorAll('.podcast-track-item').forEach(function(btn){
        btn.addEventListener('click', function(){
            const src = btn.getAttribute('data-track-src');
            const title = btn.getAttribute('data-track-title') || '';
            const cover = btn.getAttribute('data-track-cover') || '';
            if (!src) return;

            document.querySelectorAll('.podcast-track-item').forEach(function(el){ el.classList.remove('active'); });
            btn.classList.add('active');
            if (nowPlayingTitle) {
                nowPlayingTitle.textContent = title;
                nowPlayingTitle.classList.remove('d-none');
                nowPlayingTitle.hidden = false;
            }
            if (heroCover && cover) heroCover.src = cover;
            updateSocialLinks({
                spotify: btn.getAttribute('data-spotify') || '',
                apple: btn.getAttribute('data-apple') || '',
                soundcloud: btn.getAttribute('data-soundcloud') || '',
                youtube: btn.getAttribute('data-youtube') || ''
            });
            audio.src = src;
            audio.load();
            audio.play().then(function(){ setPlayingUi(true); }).catch(function(){ setPlayingUi(false); });
        });
    });
})();
</script>
@endpush
