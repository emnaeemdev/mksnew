@extends('frontend.layouts.app')

@section('title', $title ?? (app()->isLocale('ar') ? 'بودكاست محكمة' : 'Court Podcast'))
@section('meta')
    <meta name="description" content="{{ $description ?? '' }}">
@endsection

@section('styles')
<style>
.pc-page{
    --pc-brand:#2b4596;--pc-ink:#0f172a;--pc-muted:#64748b;--pc-line:#e8edf3;--pc-soft:#f7f9fc;
    width:100%;max-width:100%;margin:0 auto;padding-inline:clamp(.75rem,2vw,1.5rem)
}
.pc-shell{display:grid;gap:1.25rem;max-width:1320px;margin-inline:auto}
.pc-card{background:#fff;border:1px dashed rgb(0 101 251 / 62%);border-radius:18px;overflow:hidden}
.pc-card-pad{padding:1.25rem}
.pc-hero{display:grid;grid-template-columns:minmax(260px,34%) 1fr;align-items:stretch}
.pc-cover-wrap{position:relative;background:#e9eef6}
.pc-cover{width:100%;height:100%;object-fit:cover;display:block}
.pc-top:not(.pc-top--series) .pc-hero{align-items:start;min-height:0}
.pc-top:not(.pc-top--series) .pc-cover-wrap{aspect-ratio:1/1;height:auto;min-height:0}
.pc-top:not(.pc-top--series) .pc-cover{position:static;height:100%;aspect-ratio:1/1}
.pc-hero-body{padding:1.5rem 1.75rem;display:flex;flex-direction:column;justify-content:center}
.pc-kicker{display:inline-flex;align-items:center;gap:.4rem;color:var(--pc-brand);font-size:.82rem;font-weight:700;margin-bottom:.45rem}
.pc-title{font-size:clamp(1.35rem,2.4vw,1.85rem);font-weight:800;color:var(--pc-ink);line-height:1.45;margin:0 0 .55rem}
.pc-meta{color:var(--pc-muted);font-size:.92rem;display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem}
.pc-player{background:var(--pc-soft);border:1px solid var(--pc-line);border-radius:16px;padding:1rem}
.pc-now{font-weight:700;color:var(--pc-brand);margin-bottom:.75rem;font-size:.95rem}
.pc-controls{display:flex;align-items:center;justify-content:center;gap:.85rem;margin-bottom:.85rem}
.pc-btn{width:42px;height:42px;border:0;border-radius:50%;background:#fff;color:var(--pc-brand);border:1px solid var(--pc-line);display:inline-flex;align-items:center;justify-content:center;transition:.15s ease}
.pc-btn:hover{background:#eef3fb}
.pc-btn-main{width:56px;height:56px;background:var(--pc-brand);color:#fff;border-color:var(--pc-brand);box-shadow:0 8px 18px rgba(43,69,150,.25)}
.pc-btn-main:hover{background:#22397d;color:#fff}
.pc-seek{display:flex;align-items:center;gap:.65rem;font-size:.84rem;color:var(--pc-muted);margin-bottom:.75rem}
.pc-seek input[type=range]{flex:1;accent-color:var(--pc-brand);height:8px}
.pc-tools{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem}
.pc-volume{display:inline-flex;align-items:center;gap:.35rem;background:#fff;border:1px solid var(--pc-line);border-radius:999px;padding:.2rem .35rem}
.pc-vol-btn{width:30px;height:30px;border:0;border-radius:50%;background:transparent;color:#64748b;display:inline-flex;align-items:center;justify-content:center}
.pc-vol-btn:hover{background:#f1f5f9;color:var(--pc-brand)}
.pc-volume-pct{font-size:.8rem;color:var(--pc-muted);min-width:42px;text-align:center;font-weight:600}
.pc-tools .form-select{width:auto;min-width:84px;border-color:var(--pc-line);background:#fff;border-radius:999px}
.pc-social{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.85rem}
.pc-social a{display:none;align-items:center;gap:.4rem;text-decoration:none;border:1px solid var(--pc-line);background:#fff;color:var(--pc-ink);border-radius:999px;padding:.35rem .75rem;font-size:.84rem;font-weight:600}
.pc-social a.is-visible{display:inline-flex}
.pc-social a:hover{border-color:#c9d7f0;background:#eef3fb;color:var(--pc-brand)}
.pc-section-title{font-size:1.05rem;font-weight:800;color:var(--pc-ink);margin:0 0 1rem}
.pc-top{display:grid;gap:1.25rem;align-items:stretch}
.pc-top--series{
    --pc-series-height:500px;
    grid-template-columns:minmax(0,1fr) minmax(280px,320px);
}
.pc-top--series > .pc-card,
.pc-top--series > .pc-playlist{
    height:var(--pc-series-height);
    max-height:var(--pc-series-height);
    min-height:var(--pc-series-height);
}
.pc-top--series .pc-hero{
    height:100%;
    min-height:0;
}
.pc-top--series .pc-cover-wrap{
    height:100%;
    min-height:0;
    aspect-ratio:auto;
}
.pc-top--series .pc-cover{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    aspect-ratio:auto;
}
.pc-top--series .pc-hero-body{
    justify-content:center;
    overflow:hidden;
    min-height:0;
}
.pc-playlist{background:#fff;border:1px solid var(--pc-line);border-radius:18px;overflow:hidden;display:flex;flex-direction:column;min-height:0}
.pc-playlist-head{padding:1rem 1.15rem;font-size:.95rem;font-weight:800;color:var(--pc-ink);border-bottom:1px solid var(--pc-line);display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-shrink:0}
.pc-playlist-count{font-size:.78rem;font-weight:700;color:var(--pc-muted);background:var(--pc-soft);border:1px solid var(--pc-line);border-radius:999px;padding:.2rem .55rem}
.pc-playlist-tracks{overflow-y:auto;padding:.75rem;display:grid;gap:.55rem;flex:1;min-height:0;overscroll-behavior:contain}
.pc-track{display:grid;grid-template-columns:auto 48px 1fr auto;gap:.75rem;align-items:center;width:100%;text-align:start;border:1px solid var(--pc-line);border-radius:14px;padding:.7rem;background:#fff;cursor:pointer;transition:.15s ease}
.pc-track:hover{border-color:#c9d7f0;background:#f8fbff}
.pc-track.is-active{border-color:#b7caf0;background:#eef3fb;box-shadow:inset 3px 0 0 var(--pc-brand)}
.pc-track-num{width:26px;height:26px;border-radius:50%;background:var(--pc-soft);color:var(--pc-brand);border:1px solid var(--pc-line);display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0}
.pc-track.is-active .pc-track-num{background:var(--pc-brand);color:#fff;border-color:var(--pc-brand)}
.pc-track-thumb{width:48px;height:48px;border-radius:10px;object-fit:cover;background:var(--pc-soft);border:1px solid var(--pc-line)}
.pc-track-title{font-weight:700;color:var(--pc-ink);margin-bottom:.15rem;font-size:.88rem;line-height:1.35}
.pc-track-desc{color:var(--pc-muted);font-size:.78rem;line-height:1.45;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.pc-track-play{width:34px;height:34px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:var(--pc-soft);color:var(--pc-brand);border:1px solid var(--pc-line);flex-shrink:0}
.pc-track.is-active .pc-track-play{background:var(--pc-brand);color:#fff;border-color:var(--pc-brand)}
.pc-content{color:#334155;line-height:1.85}
.pc-related{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.85rem}
.pc-related-item{display:flex;gap:.75rem;align-items:center;padding:.75rem;border:1px solid var(--pc-line);border-radius:14px;text-decoration:none;background:#fff;transition:.15s ease}
.pc-related-item:hover{border-color:#c9d7f0;background:#f8fbff}
.pc-related-item img{width:58px;height:58px;border-radius:10px;object-fit:cover;border:1px solid var(--pc-line)}
.pc-related-item strong{display:block;color:var(--pc-ink);font-size:.92rem;line-height:1.4;margin-bottom:.15rem}
.pc-related-item small{color:var(--pc-muted)}
@media (min-width: 992px){
    .pc-hero{grid-template-columns:minmax(300px,36%) 1fr}
    .pc-hero-body{padding:1.75rem 2rem}
    .pc-player{padding:1.15rem 1.25rem}
    .pc-top--series{
        --pc-series-height:520px;
        grid-template-columns:minmax(0,1fr) minmax(300px,340px);
    }
}
@media (min-width: 1200px){
    .pc-shell{max-width:1280px}
    .pc-hero{grid-template-columns:minmax(340px,38%) 1fr}
    .pc-top--series{--pc-series-height:540px}
    .pc-related{grid-template-columns:repeat(3,minmax(0,1fr))}
}
@media (min-width: 1400px){
    .pc-shell{max-width:1360px}
    .pc-top--series{--pc-series-height:560px}
}
@media (max-width: 991px){
    .pc-top--series{grid-template-columns:1fr}
    .pc-top--series > .pc-card,
    .pc-top--series > .pc-playlist{
        height:auto;
        max-height:none;
        min-height:0;
    }
    .pc-top--series .pc-hero{min-height:380px}
}
@media (max-width: 768px){
    .pc-hero{grid-template-columns:1fr}
    .pc-top:not(.pc-top--series) .pc-cover-wrap{aspect-ratio:16/10;max-width:none;width:100%}
    .pc-hero-body{padding:1.1rem}
    .pc-top--series .pc-playlist{max-height:340px}
    .pc-meta,.pc-tools{justify-content:center}
    .pc-volume{justify-content:center}
    .pc-social{justify-content:center}
    .pc-track{grid-template-columns:auto 44px 1fr;gap:.55rem}
    .pc-track-play{display:none}
    .pc-related{grid-template-columns:1fr}
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
        : ($podcastCover ? asset('storage/'.$podcastCover) : 'https://via.placeholder.com/400x400?text=Podcast');

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

<div class="container-fluid py-4 pc-page">
    <div class="pc-shell">
        <div class="pc-top {{ $isSeries ? 'pc-top--series' : '' }}">
        <article class="pc-card">
            <div class="pc-hero">
                <div class="pc-cover-wrap">
                    <img class="pc-cover" id="podcastHeroCover" src="{{ $heroCover }}" alt="{{ $podcast->title }}">
                </div>

                <div class="pc-hero-body">
                    <div class="pc-kicker">
                        <i class="fas fa-podcast"></i>
                        {{ $isSeries
                            ? (app()->isLocale('ar') ? 'سلسلة حلقات' : 'Episode series')
                            : (app()->isLocale('ar') ? 'حلقة بودكاست' : 'Podcast episode') }}
                        @if($isSeries)
                            · {{ $tracks->count() }} {{ app()->isLocale('ar') ? 'حلقات' : 'tracks' }}
                        @endif
                    </div>

                    <h1 class="pc-title">{{ $podcast->title }}</h1>

                    <div class="pc-meta">
                        @if($podcast->published_at)
                            <span><i class="far fa-calendar-alt me-1"></i>{{ $podcast->published_at->format('Y-m-d') }}</span>
                        @endif
                        @if($isSeries && $activeTrack)
                            <span id="pcMetaNow"><i class="fas fa-headphones me-1"></i>{{ $activeTrack->displayTitle($podcast->title) }}</span>
                        @endif
                    </div>

                    @if($activeTrack)
                        <div class="pc-player" id="podcast-player-app">
                            <div class="pc-now {{ $isSeries ? '' : 'd-none' }}" id="nowPlayingTitle" @if(!$isSeries) hidden @endif>
                                {{ $activeTrack->displayTitle($podcast->title) }}
                            </div>

                            <div class="pc-controls">
                                <button type="button" class="pc-btn" id="btn-back" aria-label="رجوع 10 ثوان">
                                    <i class="fas fa-undo-alt"></i>
                                </button>
                                <button type="button" class="pc-btn pc-btn-main" id="btn-play" aria-label="تشغيل">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button type="button" class="pc-btn" id="btn-forward" aria-label="تقديم 10 ثوان">
                                    <i class="fas fa-redo-alt"></i>
                                </button>
                            </div>

                            <div class="pc-seek">
                                <span id="currentTime">0:00</span>
                                <input type="range" id="seek" min="0" max="100" value="0" aria-label="تقدّم التشغيل">
                                <span id="duration">0:00</span>
                            </div>

                            <div class="pc-tools">
                                <div class="pc-volume" aria-label="التحكم في الصوت">
                                    <button type="button" class="pc-vol-btn" id="btn-vol-down" aria-label="خفض الصوت">
                                        <i class="fas fa-volume-down"></i>
                                    </button>
                                    <span class="pc-volume-pct" id="volumePct">100%</span>
                                    <button type="button" class="pc-vol-btn" id="btn-vol-up" aria-label="رفع الصوت">
                                        <i class="fas fa-volume-up"></i>
                                    </button>
                                </div>

                                <select id="speed" class="form-select form-select-sm" aria-label="سرعة التشغيل">
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

                        <div class="pc-social" id="podcastSocialLinks">
                            <a data-social="spotify" class="{{ !empty($activeLinks['spotify']) ? 'is-visible' : '' }}"
                               href="{{ $activeLinks['spotify'] ?? '#' }}" target="_blank" rel="noopener">
                                <i class="fab fa-spotify"></i> Spotify
                            </a>
                            <a data-social="apple" class="{{ !empty($activeLinks['apple']) ? 'is-visible' : '' }}"
                               href="{{ $activeLinks['apple'] ?? '#' }}" target="_blank" rel="noopener">
                                <i class="fab fa-apple"></i> Apple
                            </a>
                            <a data-social="soundcloud" class="{{ !empty($activeLinks['soundcloud']) ? 'is-visible' : '' }}"
                               href="{{ $activeLinks['soundcloud'] ?? '#' }}" target="_blank" rel="noopener">
                                <i class="fab fa-soundcloud"></i> SoundCloud
                            </a>
                            <a data-social="youtube" class="{{ !empty($activeLinks['youtube']) ? 'is-visible' : '' }}"
                               href="{{ $activeLinks['youtube'] ?? '#' }}" target="_blank" rel="noopener">
                                <i class="fab fa-youtube"></i> YouTube
                            </a>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            {{ app()->isLocale('ar') ? 'لا يوجد ملف صوت لهذه الحلقة حالياً.' : 'No audio file is available for this episode yet.' }}
                        </div>
                    @endif
                </div>
            </div>
        </article>

        @if($isSeries)
            <aside class="pc-playlist" aria-label="{{ $podcast->seriesHeading() }}">
                <div class="pc-playlist-head">
                    <span>{{ $podcast->seriesHeading() }}</span>
                    <span class="pc-playlist-count">{{ $tracks->count() }}</span>
                </div>
                <div class="pc-playlist-tracks">
                    @foreach($tracks as $index => $track)
                        @php $trackLinks = $track->socialLinks(); @endphp
                        <button type="button"
                                class="pc-track {{ $activeTrack && $activeTrack->id === $track->id ? 'is-active' : '' }}"
                                data-track-id="{{ $track->id }}"
                                data-track-title="{{ $track->displayTitle($podcast->title) }}"
                                data-track-src="{{ route('frontend.podcasts.tracks.stream', [app()->getLocale(), $podcast->id, $track->id]) }}"
                                data-track-cover="{{ $track->coverUrl($podcastCover) }}"
                                data-spotify="{{ $trackLinks['spotify'] ?? '' }}"
                                data-apple="{{ $trackLinks['apple'] ?? '' }}"
                                data-soundcloud="{{ $trackLinks['soundcloud'] ?? '' }}"
                                data-youtube="{{ $trackLinks['youtube'] ?? '' }}"
                                @if($track->description) data-track-description="{{ e(strip_tags($track->description)) }}" @endif>
                            <span class="pc-track-num">{{ $index + 1 }}</span>
                            <img class="pc-track-thumb" src="{{ $track->coverUrl($podcastCover) }}" alt="">
                            <span>
                                <div class="pc-track-title">{{ $track->displayTitle($podcast->title) }}</div>
                                @if($track->description)
                                    <div class="pc-track-desc">{{ \Illuminate\Support\Str::limit(strip_tags($track->description), 70) }}</div>
                                @endif
                            </span>
                            <span class="pc-track-play" aria-hidden="true">
                                <i class="fas {{ $activeTrack && $activeTrack->id === $track->id ? 'fa-pause' : 'fa-play' }}"></i>
                            </span>
                        </button>
                    @endforeach
                </div>
            </aside>
        @endif
        </div>

        @if($activeTrack && $activeTrack->description)
            <section class="pc-card pc-card-pad" id="pcAboutSection">
                <h2 class="pc-section-title">{{ app()->isLocale('ar') ? 'عن الحلقة' : 'About this episode' }}</h2>
                <div class="pc-content" id="pcTrackDescription">{{ $activeTrack->description }}</div>
            </section>
        @endif

        @if($podcast->content)
            <section class="pc-card pc-card-pad">
                <h2 class="pc-section-title">{{ app()->isLocale('ar') ? 'تفاصيل إضافية' : 'More details' }}</h2>
                <div class="pc-content">{!! safe_html($podcast->content) !!}</div>
            </section>
        @endif

        <section class="pc-card pc-card-pad">
            <h2 class="pc-section-title">{{ app()->isLocale('ar') ? 'بودكاست أخرى' : 'More podcasts' }}</h2>
            <div class="pc-related">
                @foreach(\App\Models\Podcast::where('is_published', true)->where('id', '!=', $podcast->id)->latest('published_at')->take(6)->get() as $item)
                    <a class="pc-related-item" href="{{ route('frontend.podcasts.show', [app()->getLocale(), $item->id]) }}">
                        <img src="{{ $item->cover_image_path ? asset('storage/'.$item->cover_image_path) : 'https://via.placeholder.com/80x80?text=P' }}"
                             alt="{{ $item->title }}">
                        <span>
                            <strong>{{ $item->title }}</strong>
                            <small>{{ optional($item->published_at)->format('Y-m-d') }}</small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</div>
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
    const pcMetaNow = document.getElementById('pcMetaNow');
    const heroCover = document.getElementById('podcastHeroCover');
    const socialBox = document.getElementById('podcastSocialLinks');
    const trackDescription = document.getElementById('pcTrackDescription');
    const aboutSection = document.getElementById('pcAboutSection');
    const trackButtons = Array.from(document.querySelectorAll('.pc-track'));
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
        volumePct.textContent = Math.round(v * 100) + '%';
    }

    function setPlayingUi(isPlaying){
        btnPlay.innerHTML = isPlaying ? '<i class="fas fa-pause"></i>' : '<i class="fas fa-play"></i>';
        document.querySelectorAll('.pc-track').forEach(function(el){
            const icon = el.querySelector('.pc-track-play i');
            if (!icon) return;
            icon.className = (el.classList.contains('is-active') && isPlaying) ? 'fas fa-pause' : 'fas fa-play';
        });
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

    function selectTrack(btn, autoplay){
        const src = btn.getAttribute('data-track-src');
        const title = btn.getAttribute('data-track-title') || '';
        const cover = btn.getAttribute('data-track-cover') || '';
        if (!src) return false;

        trackButtons.forEach(function(el){ el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        if (nowPlayingTitle) {
            nowPlayingTitle.textContent = title;
            nowPlayingTitle.classList.remove('d-none');
            nowPlayingTitle.hidden = false;
        }
        if (pcMetaNow) {
            pcMetaNow.innerHTML = '<i class="fas fa-headphones me-1"></i>' + title;
        }
        if (heroCover && cover) heroCover.src = cover;
        const desc = btn.getAttribute('data-track-description') || '';
        if (trackDescription) {
            trackDescription.textContent = desc;
        }
        if (aboutSection) {
            aboutSection.style.display = desc ? '' : 'none';
        }
        updateSocialLinks({
            spotify: btn.getAttribute('data-spotify') || '',
            apple: btn.getAttribute('data-apple') || '',
            soundcloud: btn.getAttribute('data-soundcloud') || '',
            youtube: btn.getAttribute('data-youtube') || ''
        });
        audio.src = src;
        audio.load();
        btn.scrollIntoView({ block: 'nearest', behavior: 'smooth' });

        if (autoplay !== false) {
            audio.play().then(function(){ setPlayingUi(true); }).catch(function(){ setPlayingUi(false); });
        } else {
            setPlayingUi(false);
        }
        return true;
    }

    function playNextTrack(){
        if (trackButtons.length < 2) return;
        const currentIndex = trackButtons.findIndex(function(btn){ return btn.classList.contains('is-active'); });
        if (currentIndex < 0 || currentIndex >= trackButtons.length - 1) return;
        selectTrack(trackButtons[currentIndex + 1], true);
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
    audio.addEventListener('ended', playNextTrack);

    trackButtons.forEach(function(btn){
        btn.addEventListener('click', function(){
            selectTrack(btn, true);
        });
    });
})();
</script>
@endpush
