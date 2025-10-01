@extends('frontend.layouts.app')

@section('title', $title ?? (app()->isLocale('ar') ? 'بودكاست محكمة' : 'Court Podcast'))
@section('meta')
    <meta name="description" content="{{ $description ?? '' }}">
@endsection

@section('styles')
<style>
.player{box-shadow:0 4px 20px rgba(0,0,0,.08);border-radius:16px;overflow:hidden;background:#fff}
.player-cover{width:100%;max-height:360px;object-fit:cover;background:#f7f7f7}
.player-body{padding:16px}
.player-title{font-weight:800;font-size:1.3rem;margin-bottom:6px}
.player-controls{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.player-btn{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid #e5e5e5;background:#fff}
.player-btn:hover{background:#f0f0f0}
.seek{flex:1}
.speed, .volume{min-width:120px}
.wave{height:64px;background:#fafafa;border-radius:10px}
.badges a{margin-inline-end:8px;margin-bottom:8px}
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
                    <div class="d-flex align-items-center justify-content-center">
                        <!-- زر الرجوع (الذي يعيد التشغيل إلى نقطة سابقة) -->
                        <button class="player-btn" id="btn-back"><i class="fa-solid fa-rotate-left"></i></button>

                        <!-- زر التشغيل/الإيقاف في المنتصف -->
                        <button class="player-btn" id="btn-play"><i class="fa-solid fa-play"></i></button>

                        <!-- زر التقديم (الذي ينتقل إلى نقطة لاحقة في التشغيل) -->
                        <button class="player-btn" id="btn-forward"><i class="fa-solid fa-rotate-right"></i></button>
                    </div>
                    
                    <div class="player-controls mb-2">
                        <!-- بار التشغيل -->
                        <div class="d-flex align-items-center justify-content-center w-100">
                            <span id="currentTime">0:00</span>
                            <input type="range" id="seek" class="form-range seek" min="0" max="100" value="0">
                            <span id="duration">0:00</span>
                        </div>
                        
                        <!-- التحكم في الصوت -->
                        <div class="d-flex align-items-center volume" style="gap:6px;">
                            <i class="fa-solid fa-volume-low"></i>
                            <input type="range" id="volume" class="form-range" min="0" max="1" step="0.01" value="0.8">
                        </div>

                        <!-- التحكم في السرعة -->
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
                    <ul class="list-unstyled m-0">
                        @foreach(\App\Models\Podcast::where('is_published', true)->latest('published_at')->take(8)->get() as $item)
                            <li class="mb-3">
                                <a class="text-decoration-none" href="{{ route('frontend.podcasts.show', [app()->getLocale(), $item->id]) }}">{{ $item->title }}</a>
                            </li>
                        @endforeach
                    </ul>
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
    const volume = document.getElementById('volume');
    const btnDownload = document.getElementById('btn-download');

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

    btnPlay.addEventListener('click', ()=>{
        if(audio.paused){ audio.play(); btnPlay.innerHTML = '<i class="fa-solid fa-pause"></i>'; }
        else { audio.pause(); btnPlay.innerHTML = '<i class="fa-solid fa-play"></i>'; }
    });
    btnBack.addEventListener('click', ()=>{ audio.currentTime = Math.max(0, audio.currentTime - 15); });
    btnForward.addEventListener('click', ()=>{ audio.currentTime = Math.min(audio.duration||0, audio.currentTime + 15); });
    speed.addEventListener('change', ()=>{ audio.playbackRate = parseFloat(speed.value); });
    volume.addEventListener('input', ()=>{ audio.volume = parseFloat(volume.value); });

    seek.addEventListener('input', ()=>{ seeking = true; });
    seek.addEventListener('change', ()=>{ if(audio.duration){ audio.currentTime = (seek.value/100) * audio.duration; } seeking = false; });

    audio.addEventListener('timeupdate', updateSeek);
    audio.addEventListener('loadedmetadata', updateSeek);

    btnDownload.addEventListener('click', ()=>{
        const src = audio.getAttribute('src');
        if(!src) return;
        const a = document.createElement('a');
        a.href = src;
        a.download = 'podcast-{{ $podcast->id }}.mp3';
        document.body.appendChild(a);
        a.click();
        a.remove();
    });
})();
</script>
@endpush