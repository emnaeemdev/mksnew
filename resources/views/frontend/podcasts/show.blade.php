@extends('frontend.layouts.app')

@section('title', $title ?? (app()->isLocale('ar') ? 'بودكاست محكمة' : 'Court Podcast'))
@section('meta')
    <meta name="description" content="{{ $description ?? '' }}">
@endsection

@section('styles')
<style>
.player{box-shadow:0 4px 20px rgba(0,0,0,.08);border-radius:16px;overflow:hidden;background:#fff}
.player-cover{width:100%;height:360px;object-fit:contain;background:#f7f7f7}
.player-body{padding:16px}
.player-title{font-weight:800;font-size:1.3rem;margin-bottom:6px}
.player-controls{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.player-btn{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid #e5e5e5;background:#fff}
.player-btn:hover{background:#f0f0f0}
.seek{flex:1}
.speed, .volume{min-width:120px}
.wave{height:64px;background:#fafafa;border-radius:10px}
.badges a{margin-inline-end:8px;margin-bottom:8px}

/* تحسين شكل التحكم في الصوت */
.volume-control{display:inline-flex;align-items:center;gap:8px}
.volume-range{flex:1;appearance:none;height:6px;border-radius:999px;background:linear-gradient(90deg,#4caf50,#8bc34a)}
.volume-range::-webkit-slider-thumb{appearance:none;width:18px;height:18px;border-radius:50%;background:#fff;border:2px solid #4caf50;box-shadow:0 0 0 3px rgba(76,175,80,.15)}
.volume-range::-moz-range-thumb{width:18px;height:18px;border-radius:50%;background:#fff;border:2px solid #4caf50}

/* تحسين شكل حلقات أخرى */
.mini-episode{background:#fff;border:1px solid #eee;border-radius:12px;transition:transform .15s,box-shadow .15s}
.mini-episode:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.08)}
.mini-thumb{width:72px;height:72px;object-fit:cover;background:#f7f7f7}
.mini-title{font-weight:700;color:#222}
.mini-meta{color:#777;font-size:.85rem;padding-right: 7px;}
.clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;padding-right: 7px;}
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="player mb-3">
                <img class="player-cover" src="{{ $podcast->cover_image_path ? asset('storage/'.$podcast->cover_image_path) : 'https://via.placeholder.com/1200x600?text=Podcast' }}" alt="{{ $podcast->title }}">
                <div class="player-body">
                    <div class="player-title">{{ $podcast->title }}</div>
                    <div class="text-muted mb-2">
                        <i class="fa-regular fa-calendar"></i>
                        {{ optional($podcast->published_at)->format('Y-m-d') ?? '' }}
                    </div>
<!-- إضافة Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<div class="d-flex align-items-center justify-content-center">
    <!-- زر الرجوع 10 ثواني -->
    <button class="player-btn" id="btn-back">
        <i class="material-icons">replay_10</i>
    </button>

    <!-- زر التشغيل -->
    <button class="player-btn" id="btn-play">
        <i class="fa-solid fa-play"></i>
    </button>

    <!-- زر التقديم 10 ثواني -->
    <button class="player-btn" id="btn-forward">
        <i class="material-icons">forward_10</i>
    </button>
</div>

                    
                    <div class="player-controls mb-2">
                        <!-- بار التشغيل -->
                        <div class="d-flex align-items-center justify-content-center w-100">
                            <span id="currentTime">0:00</span>
                            <input type="range" id="seek" class="form-range seek" min="0" max="100" value="0">
                            <span id="duration">0:00</span>
                        </div>
                        
                      
                        <div class="volume-control">
                            <button class="player-btn" id="btn-vol-down" aria-label="خفض الصوت"><i class="fa-solid fa-volume-down"></i></button>
                            <button class="player-btn" id="btn-vol-up" aria-label="رفع الصوت"><i class="fa-solid fa-volume-up"></i></button>
                            <span id="volumePct" class="text-muted small">80%</span>
                        </div>

                  
                        <select id="speed" class="form-select form-select-sm speed">
                            <option value="0.75">0.75x</option>
                            <option value="1" selected>1x</option>
                            <option value="1.25">1.25x</option>
                            <option value="1.5">1.5x</option>
                            <option value="1.75">1.75x</option>
                            <option value="2">2x</option>
                        </select>
                    </div>

                    <audio id="audio" preload="metadata" src="{{ $podcast->audio_path ? route('frontend.podcasts.stream', [app()->getLocale(), $podcast->id]) : '' }}"></audio>

                    <div class="badges mt-3">
                        @if($podcast->spotify_url)
                            <a class="btn btn-sm btn-success" href="{{ $podcast->spotify_url }}" target="_blank"><i class="fa-brands fa-spotify"></i> Spotify</a>
                        @endif
                        @if($podcast->apple_podcasts_url)
                            <a class="btn btn-sm btn-dark" href="{{ $podcast->apple_podcasts_url }}" target="_blank"><i class="fa-brands fa-apple"></i> Apple</a>
                        @endif
                        @if($podcast->soundcloud_url)
                            <a class="btn btn-sm btn-warning" href="{{ $podcast->soundcloud_url }}" target="_blank"><i class="fa-brands fa-soundcloud"></i> SoundCloud</a>
                        @endif
                        @if($podcast->youtube_url)
                            <a class="btn btn-sm btn-danger" href="{{ $podcast->youtube_url }}" target="_blank"><i class="fa-brands fa-youtube"></i> YouTube</a>
                        @endif
                    </div>
                </div>
            </div>

            @if($podcast->content)
                <div class="card shadow-sm">
                    <div class="card-body">
                        {!! $podcast->content !!}
                    </div>
                </div>
            @endif
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">{{ app()->isLocale('ar') ? 'حلقات أخرى' : 'More episodes' }}</h5>
                    <div class="row g-3">
                        @foreach(\App\Models\Podcast::where('is_published', true)->latest('published_at')->take(8)->get() as $item)
                            <div class="col-12">
                                <a class="text-decoration-none" href="{{ route('frontend.podcasts.show', [app()->getLocale(), $item->id]) }}">
                                    <div class="mini-episode d-flex align-items-center p-2">
                                        <img class="mini-thumb rounded me-2" src="{{ $item->cover_image_path ? asset('storage/'.$item->cover_image_path) : 'https://via.placeholder.com/120x120?text=Podcast' }}" alt="{{ $item->title }}">
                                        <div class="flex-grow-1">
                                            <div class="mini-title clamp-2 mb-1">{{ $item->title }}</div>
                                            <div class="mini-meta"><i class="fa-regular fa-calendar"></i> {{ optional($item->published_at)->format('Y-m-d') ?? '' }}</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
    const audio = document.getElementById('audio');
    const btnPlay = document.getElementById('btn-play');
    const btnBack = document.getElementById('btn-back');
    const btnForward = document.getElementById('btn-forward');
    const seek = document.getElementById('seek');
    const currentTimeEl = document.getElementById('currentTime');
    const durationEl = document.getElementById('duration');
    const speed = document.getElementById('speed');
    const btnDownload = document.getElementById('btn-download');

    const btnVolUp = document.getElementById('btn-vol-up');
    const btnVolDown = document.getElementById('btn-vol-down');
    const volumePct = document.getElementById('volumePct');

    let seeking = false;

    const format = (s)=>{
        s = Math.floor(s||0);
        const m = Math.floor(s/60);
        const ss = (s%60).toString().padStart(2,'0');
        return `${m}:${ss}`;
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
        volumePct.textContent = `${Math.round(v*100)}%`;
    }

    // ضبط مستوى الصوت الابتدائي
    setVolume(1);

    btnPlay.addEventListener('click', ()=>{
        if(audio.paused){ audio.play(); btnPlay.innerHTML = '<i class="fa-solid fa-pause"></i>'; }
        else { audio.pause(); btnPlay.innerHTML = '<i class="fa-solid fa-play"></i>'; }
    });
    btnBack.addEventListener('click', ()=>{ audio.currentTime = Math.max(0, audio.currentTime - 10); });
    btnForward.addEventListener('click', ()=>{ audio.currentTime = Math.min(audio.duration||0, audio.currentTime + 10); });
    speed.addEventListener('change', ()=>{ audio.playbackRate = parseFloat(speed.value); });

    // التحكم في الصوت بالأزرار فقط
    btnVolUp.addEventListener('click', ()=> setVolume(audio.volume + 0.1));
    btnVolDown.addEventListener('click', ()=> setVolume(audio.volume - 0.1));

    seek.addEventListener('input', ()=>{ seeking = true; });
    seek.addEventListener('change', ()=>{ if(audio.duration){ audio.currentTime = (seek.value/100) * audio.duration; } seeking = false; });

    audio.addEventListener('timeupdate', updateSeek);
    audio.addEventListener('loadedmetadata', ()=>{ updateSeek(); });
})();
</script>
@endpush