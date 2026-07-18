@extends('admin.layouts.app')

@section('content')
@php
    $trackCount = $podcast->tracks->count();
    $isSeries = $trackCount > 1;
@endphp
<div class="container-fluid py-3">
    <h4 class="mb-3">تعديل بودكاست</h4>
    <p class="text-muted mb-3">
        @if($isSeries)
            هذا بودكاست متعدد الحلقات ({{ $trackCount }} حلقات). اضغط على عنوان أي حلقة لفتح تفاصيلها.
        @else
            بودكاست بحلقة واحدة. لإضافة سلسلة حلقات استخدم زر «إضافة حلقة» أسفل قائمة الحلقات.
        @endif
    </p>
    <div>
                    <a target="_blank" href="{{ route('frontend.podcasts.show', [app()->getLocale(), $podcast->id]) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-eye me-2"></i>
                        عرض
                    </a>
                    <a href="{{ route('admin.podcasts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>

    <form action="{{ route('admin.podcasts.update', $podcast) }}" method="post" enctype="multipart/form-data" id="podcastForm" novalidate>
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header">بيانات البودكاست</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">عنوان البودكاست <span class="text-danger">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $podcast->title) }}" class="form-control" required>
                </div>
                @include('admin.partials.keyword-picker', [
                    'keywordScope' => 'podcast',
                    'selectedKeywords' => $podcast->keywords,
                ])
                <div class="mb-3" id="seriesLabelWrap" @if(!$isSeries) style="display:none" @endif>
                    <label class="form-label">عنوان قسم الحلقات</label>
                    <input type="text" name="series_label" value="{{ old('series_label', $podcast->series_label ?: 'حلقات هذه السلسلة') }}" class="form-control" placeholder="حلقات هذه السلسلة">
                </div>
                <div class="mb-3">
                    <label class="form-label">المحتوى / الوصف</label>
                    <textarea name="content" class="form-control" rows="8">{{ old('content', $podcast->content) }}</textarea>
                </div>

                <div class="border-top pt-3">
                    <label class="form-label">صورة غلاف البودكاست</label>
                    <p class="small text-muted mb-2">هذه صورة البودكاست ككل (وليست صورة حلقة). صور الحلقات تُدار داخل كل حلقة بالأسفل.</p>
                    @if($podcast->cover_image_path)
                        <img src="{{ asset('storage/'.$podcast->cover_image_path) }}" class="img-fluid mb-2 d-block rounded" style="max-height:150px;object-fit:cover" alt="">
                    @endif
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <span id="tracksCardTitle">{{ $isSeries ? 'الحلقات' : 'الملف الصوتي' }}</span>
            </div>
            <div class="card-body">
                <div class="accordion" id="tracksAccordion">
                    @forelse($podcast->tracks as $index => $track)
                        @php
                            $itemId = 'track_item_' . $track->id;
                            $collapseId = 'track_collapse_' . $track->id;
                            $openFirst = $trackCount === 1 || $index === 0;
                            $trackTitle = $track->displayTitle($podcast->title);
                        @endphp
                        <div class="accordion-item existing-track-row mb-2 border rounded overflow-hidden" data-track-id="{{ $track->id }}">
                            <h2 class="accordion-header" id="{{ $itemId }}">
                                <button class="accordion-button {{ $openFirst ? '' : 'collapsed' }} py-3"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#{{ $collapseId }}"
                                        aria-expanded="{{ $openFirst ? 'true' : 'false' }}"
                                        aria-controls="{{ $collapseId }}">
                                    <span class="track-accordion-head">
                                        <img src="{{ $track->coverUrl($podcast->cover_image_path) }}" alt="" class="track-accordion-thumb">
                                        <span class="track-accordion-text">
                                            <strong class="track-accordion-title">{{ $trackTitle }}</strong>
                                            <small class="track-accordion-meta text-muted">حلقة {{ $index + 1 }} · ترتيب {{ $track->sort_order }}</small>
                                        </span>
                                    </span>
                                </button>
                            </h2>
                            <div id="{{ $collapseId }}"
                                 class="accordion-collapse collapse {{ $openFirst ? 'show' : '' }}"
                                 data-bs-parent="#tracksAccordion"
                                 aria-labelledby="{{ $itemId }}">
                                <div class="accordion-body">
                                    @if($trackCount > 1)
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="delete_track_ids[]" value="{{ $track->id }}" id="del_track_{{ $track->id }}">
                                            <label class="form-check-label text-danger" for="del_track_{{ $track->id }}">حذف هذه الحلقة عند التحديث</label>
                                        </div>
                                    @endif
                                    <audio controls preload="metadata" class="w-100 mb-3">
                                        <source src="{{ route('admin.podcasts.tracks.stream', [$podcast, $track]) }}" type="audio/mpeg">
                                    </audio>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label">عنوان الحلقة</label>
                                            <input type="text" name="existing_tracks[{{ $track->id }}][title]" value="{{ $track->title }}" class="form-control track-title-input" placeholder="اتركه فارغاً لاستخدام عنوان البودكاست">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">الترتيب</label>
                                            <input type="number" name="existing_tracks[{{ $track->id }}][sort_order]" value="{{ $track->sort_order }}" class="form-control" min="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">صورة الحلقة</label>
                                            <input type="file" name="existing_tracks[{{ $track->id }}][cover]" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">استبدال ملف الصوت</label>
                                            <input type="file" name="existing_tracks[{{ $track->id }}][audio]" class="form-control" accept="audio/*">
                                            <small class="text-muted">اتركه فارغاً للإبقاء على الملف الحالي</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">تفاصيل الحلقة</label>
                                            <textarea name="existing_tracks[{{ $track->id }}][description]" class="form-control" rows="2">{{ $track->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="track-social border-top pt-3 mt-3" @if(!$isSeries) style="display:none" @endif>
                                        <div class="small text-muted mb-2">روابط المنصات لهذه الحلقة</div>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label">Spotify</label>
                                                <input type="text" name="existing_tracks[{{ $track->id }}][spotify_url]" value="{{ $track->spotify_url }}" class="form-control" inputmode="url" placeholder="https://">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Apple Podcasts</label>
                                                <input type="text" name="existing_tracks[{{ $track->id }}][apple_podcasts_url]" value="{{ $track->apple_podcasts_url }}" class="form-control" inputmode="url" placeholder="https://">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">SoundCloud</label>
                                                <input type="text" name="existing_tracks[{{ $track->id }}][soundcloud_url]" value="{{ $track->soundcloud_url }}" class="form-control" inputmode="url" placeholder="https://">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">YouTube</label>
                                                <input type="text" name="existing_tracks[{{ $track->id }}][youtube_url]" value="{{ $track->youtube_url }}" class="form-control" inputmode="url" placeholder="https://">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-warning mb-0" id="noTracksAlert">لا يوجد ملف صوتي. أضف حلقة من الزر بالأسفل.</div>
                    @endforelse
                </div>

                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary" id="addTrackBtn">
                        <i class="fas fa-plus"></i> إضافة حلقة
                    </button>
                    <small class="text-muted text-center">الحلقة الجديدة تُفتح تلقائياً أسفل القائمة بعد الضغط على الزر.</small>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">النشر والترتيب</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">تاريخ النشر</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($podcast->published_at)->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الترتيب</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $podcast->sort_order) }}" class="form-control" min="0">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" @checked(old('is_published', $podcast->is_published))>
                            <label class="form-check-label" for="is_published">منشور</label>
                        </div>
                    </div>
                </div>

                <div id="podcastSocialFields" class="mt-3" @if($isSeries) style="display:none" @endif>
                    <p class="small text-muted mb-2">روابط المنصات (لحلقة واحدة). عند تعدد الحلقات تُدار الروابط لكل حلقة.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Spotify</label>
                            <input type="text" name="spotify_url" value="{{ old('spotify_url', $podcast->spotify_url) }}" class="form-control" inputmode="url" placeholder="https://">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apple Podcasts</label>
                            <input type="text" name="apple_podcasts_url" value="{{ old('apple_podcasts_url', $podcast->apple_podcasts_url) }}" class="form-control" inputmode="url" placeholder="https://">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SoundCloud</label>
                            <input type="text" name="soundcloud_url" value="{{ old('soundcloud_url', $podcast->soundcloud_url) }}" class="form-control" inputmode="url" placeholder="https://">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">YouTube</label>
                            <input type="text" name="youtube_url" value="{{ old('youtube_url', $podcast->youtube_url) }}" class="form-control" inputmode="url" placeholder="https://">
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
                        <button type="submit" class="btn btn-primary" id="podcastSubmitBtn">تحديث</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
#tracksAccordion .accordion-button {
    gap: .75rem;
}
#tracksAccordion .accordion-button:not(.collapsed) {
    background: #eef3fb;
    color: #2b4596;
    box-shadow: none;
}
#tracksAccordion .accordion-button:focus {
    box-shadow: none;
    border-color: rgba(43, 69, 150, .25);
}
#tracksAccordion .accordion-item {
    border-color:rgb(26, 93, 192) !important;
}
#tracksAccordion .accordion-body {
    background: #fff;
}
#tracksAccordion .track-accordion-head {
    display: flex;
    align-items: center;
    gap: .75rem;
    min-width: 0;
    flex: 1 1 auto;
    padding-inline-end: .5rem;
    text-align: start;
}
#tracksAccordion .track-accordion-thumb {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 8px;
    flex: 0 0 auto;
}
#tracksAccordion .track-accordion-text {
    display: flex;
    flex-direction: column;
    gap: .15rem;
    min-width: 0;
    flex: 1 1 auto;
}
#tracksAccordion .track-accordion-title {
    display: block;
    font-size: .95rem;
    line-height: 1.35;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
}
#tracksAccordion .track-accordion-meta {
    display: block;
    font-size: .78rem;
    line-height: 1.3;
    white-space: nowrap;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('dashboard/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('js/admin-tinymce.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof initAdminTinyMCE === 'function') {
        initAdminTinyMCE('textarea[name=content]', { directionality: 'rtl' });
    }

    let trackIndex = 0;
    const form = document.getElementById('podcastForm');
    const accordion = document.getElementById('tracksAccordion');
    const addBtn = document.getElementById('addTrackBtn');
    const podcastSocial = document.getElementById('podcastSocialFields');
    const seriesLabelWrap = document.getElementById('seriesLabelWrap');
    const tracksCardTitle = document.getElementById('tracksCardTitle');
    const noTracksAlert = document.getElementById('noTracksAlert');

    function totalTracks() {
        return accordion.querySelectorAll('.existing-track-row, .track-row').length;
    }

    function syncUi() {
        const multi = totalTracks() > 1;
        if (podcastSocial) podcastSocial.style.display = multi ? 'none' : '';
        if (seriesLabelWrap) seriesLabelWrap.style.display = multi ? '' : 'none';
        if (tracksCardTitle) tracksCardTitle.textContent = multi ? 'الحلقات' : 'الملف الصوتي';
        document.querySelectorAll('.existing-track-row .track-social, .track-row .track-social').forEach(function (el) {
            el.style.display = multi ? '' : 'none';
        });
        if (noTracksAlert) {
            noTracksAlert.style.display = totalTracks() === 0 ? '' : 'none';
        }
    }

    function collapseOthers(exceptEl) {
        accordion.querySelectorAll('.accordion-collapse.show').forEach(function (el) {
            if (el === exceptEl) return;
            const inst = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el, { toggle: false });
            inst.hide();
        });
    }

    function openTrackPanel(collapseEl) {
        if (!collapseEl) return;
        collapseOthers(collapseEl);
        const inst = bootstrap.Collapse.getInstance(collapseEl) || new bootstrap.Collapse(collapseEl, { toggle: false });
        inst.show();
    }

    addBtn.addEventListener('click', function () {
        const uid = 'new_' + Date.now() + '_' + trackIndex;
        const collapseId = 'track_collapse_' + uid;
        const headerId = 'track_item_' + uid;
        const wrap = document.createElement('div');
        wrap.className = 'accordion-item track-row mb-2 border rounded overflow-hidden';
        wrap.innerHTML =
            '<h2 class="accordion-header" id="' + headerId + '">' +
            '<button class="accordion-button py-3" type="button" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" aria-expanded="true" aria-controls="' + collapseId + '">' +
            '<span class="track-accordion-head"><span class="track-accordion-text">' +
            '<strong class="track-accordion-title">حلقة جديدة</strong>' +
            '<small class="track-accordion-meta text-muted">سيتم حفظها بعد الضغط على تحديث</small>' +
            '</span></span></button></h2>' +
            '<div id="' + collapseId + '" class="accordion-collapse collapse show" data-bs-parent="#tracksAccordion" aria-labelledby="' + headerId + '">' +
            '<div class="accordion-body">' +
            '<div class="d-flex justify-content-end mb-2">' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-track">حذف هذه الحلقة</button>' +
            '</div>' +
            '<div class="row g-2">' +
            '<div class="col-md-6"><label class="form-label">عنوان الحلقة</label><input type="text" name="tracks[' + trackIndex + '][title]" class="form-control track-title-input"></div>' +
            '<div class="col-md-6"><label class="form-label">ملف الصوت <span class="text-danger">*</span></label><input type="file" name="tracks[' + trackIndex + '][audio]" class="form-control track-audio-input" accept="audio/*"></div>' +
            '<div class="col-md-6"><label class="form-label">صورة الحلقة</label><input type="file" name="tracks[' + trackIndex + '][cover]" class="form-control" accept="image/*"></div>' +
            '<div class="col-md-6"><label class="form-label">تفاصيل الحلقة</label><textarea name="tracks[' + trackIndex + '][description]" class="form-control" rows="2"></textarea></div>' +
            '</div>' +
            '<div class="track-social border-top pt-3 mt-3">' +
            '<div class="small text-muted mb-2">روابط المنصات لهذه الحلقة</div>' +
            '<div class="row g-2">' +
            '<div class="col-md-6"><label class="form-label">Spotify</label><input type="text" name="tracks[' + trackIndex + '][spotify_url]" class="form-control" inputmode="url" placeholder="https://"></div>' +
            '<div class="col-md-6"><label class="form-label">Apple Podcasts</label><input type="text" name="tracks[' + trackIndex + '][apple_podcasts_url]" class="form-control" inputmode="url" placeholder="https://"></div>' +
            '<div class="col-md-6"><label class="form-label">SoundCloud</label><input type="text" name="tracks[' + trackIndex + '][soundcloud_url]" class="form-control" inputmode="url" placeholder="https://"></div>' +
            '<div class="col-md-6"><label class="form-label">YouTube</label><input type="text" name="tracks[' + trackIndex + '][youtube_url]" class="form-control" inputmode="url" placeholder="https://"></div>' +
            '</div></div></div></div>';

        accordion.appendChild(wrap);
        const collapseEl = wrap.querySelector('.accordion-collapse');
        openTrackPanel(collapseEl);
        trackIndex++;
        syncUi();
        wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    accordion.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-track');
        if (!btn) return;
        btn.closest('.track-row').remove();
        syncUi();
    });

    accordion.addEventListener('input', function (e) {
        if (!e.target.classList.contains('track-title-input')) return;
        const item = e.target.closest('.accordion-item');
        const titleEl = item && item.querySelector('.track-accordion-title');
        if (!titleEl) return;
        const val = e.target.value.trim();
        titleEl.textContent = val || (item.classList.contains('track-row') ? 'حلقة جديدة' : 'حلقة بدون عنوان');
    });

    form.addEventListener('submit', function (e) {
        if (typeof tinymce !== 'undefined' && tinymce.editors) {
            tinymce.editors.forEach(function (editor) {
                editor.save();
            });
        }

        const titleInput = form.querySelector('input[name="title"]');
        if (!titleInput || !titleInput.value.trim()) {
            e.preventDefault();
            e.stopImmediatePropagation();
            titleInput && titleInput.focus();
            alert('عنوان البودكاست مطلوب');
            return;
        }

        const newRows = form.querySelectorAll('.track-row');
        for (let i = 0; i < newRows.length; i++) {
            const audio = newRows[i].querySelector('.track-audio-input');
            if (!audio || !audio.files || audio.files.length === 0) {
                e.preventDefault();
                e.stopImmediatePropagation();
                openTrackPanel(newRows[i].querySelector('.accordion-collapse'));
                audio && audio.focus();
                alert('يجب اختيار ملف صوت لكل حلقة جديدة قبل التحديث');
                return;
            }
        }
    }, true);

    syncUi();
});
</script>
@endpush
