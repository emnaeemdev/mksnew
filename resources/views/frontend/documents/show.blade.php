@extends('frontend.layouts.app')

@section('title', $document->title)
@section('description', $document->excerpt ?: Str::limit(strip_tags($document->content), 160))

@section('content')
<div class="container py-5">
    <!-- التنقل -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb {{ app()->getLocale() == 'ar' ?  : '' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                            @if(app()->getLocale() == 'ar')
                                الرئيسية
                                <i class="fas fa-home ms-1"></i>
                            @else
                                <i class="fas fa-home me-1"></i>
                                Home
                            @endif
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('frontend.documents.index') }}">
                            {{ app()->getLocale() == 'ar' ? 'الوثائق' : 'Documents' }}
                        </a>
                    </li>
                    @if($document->section && $document->section->slug)
                        <li class="breadcrumb-item"><a href="{{ route('frontend.documents.section', [app()->getLocale(), $document->section->slug]) }}">{{ app()->getLocale() === 'ar' ? $document->section->name : $document->section->name_en }}</a></li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('frontend.documents.index') }}">{{ app()->getLocale() == 'ar' ? 'الوثائق' : 'Documents' }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ Str::limit($document->title, 50) }}</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <!-- المحتوى الرئيسي -->
        <div class="col-lg-8">
            <!-- رأس الوثيقة -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <!-- العلامات والحالة -->
                    <div class="mb-3">
                        @if($document->section && $document->section->name)
                            <span class="badge bg-primary me-2">{{ app()->getLocale() === 'ar' ? $document->section->name : $document->section->name_en }}</span>
                        @else
                            <span class="badge bg-secondary me-2">{{ app()->getLocale() == 'ar' ? 'قسم غير محدد' : 'Undefined Section' }}</span>
                        @endif
                        @if($document->is_featured)
                            <span class="badge bg-warning text-dark me-2">مميز</span>
                        @endif
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-eye me-1"></i>
                            {{ number_format($document->views_count) }} مشاهدة
                        </span>
                    </div>
                    
                    <!-- العنوان -->
                    <h1 class="display-6 fw-bold text-dark mb-3">{{ $document->title }}</h1>
                    
                    <!-- الملخص -->
                    @if($document->excerpt)
                        <div class="alert alert-info border-0">
                            <h6 class="alert-heading mb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                ملخص الوثيقة
                            </h6>
                            <p class="mb-0 lead">{{ $document->excerpt }}</p>
                        </div>
                    @endif
                    
                    <!-- معلومات النشر -->
                    <div class="d-flex flex-wrap align-items-center text-muted mb-4">
                        <div class="me-4 mb-2">
                            <i class="fas fa-calendar me-1"></i>
                            <span>{{ $document->created_at->format('Y-m-d') }}</span>
                        </div>
                        <div class="me-4 mb-2">
                            <i class="fas fa-clock me-1"></i>
                            <span>{{ $document->created_at->diffForHumans() }}</span>
                        </div>
                        @if($document->updated_at != $document->created_at)
                            <div class="me-4 mb-2">
                                <i class="fas fa-edit me-1"></i>
                                <span>آخر تحديث: {{ $document->updated_at->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- أزرار المشاركة -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button class="btn btn-outline-primary btn-sm" onclick="shareDocument('facebook')">
                            <i class="fab fa-facebook-f me-1"></i> فيسبوك
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="shareDocument('twitter')">
                            <i class="fab fa-twitter me-1"></i> تويتر
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="shareDocument('whatsapp')">
                            <i class="fab fa-whatsapp me-1"></i> واتساب
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyDocumentLink(this)">
                            <i class="fas fa-link me-1"></i> نسخ الرابط
                        </button>
                        <button class="btn btn-outline-dark btn-sm" onclick="printDocument()">
                            <i class="fas fa-print me-1"></i> طباعة
                        </button>
                    </div>
                </div>
            </div>
            
            
            
            <!-- محتوى الوثيقة -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="document-content">
                        {!! $document->content !!}
                    </div>
                </div>
            </div>

            
            <!-- إحصائيات القسم -->
            {{-- <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        إحصائيات {{ app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'قسم غير محدد') : (optional($document->section)->name_en ?? 'Undefined Section') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 text-primary mb-1">{{ $sectionStats['total_documents'] }}</div>
                            <small class="text-muted">وثيقة</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 text-success mb-1">{{ number_format($sectionStats['total_views']) }}</div>
                            <small class="text-muted">مشاهدة</small>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        @if($document->section && $document->section->slug)
                            <a href="{{ route('frontend.documents.section', [app()->getLocale(), $document->section->slug]) }}" 
                               class="btn btn-sm btn-outline-primary">
                                استكشاف القسم
                            </a>
                        @else
                            <span class="btn btn-sm btn-outline-secondary disabled">
                                القسم غير متاح
                            </span>
                        @endif
                    </div>
                </div>
            </div> --}}
        </div>
        <!-- الشريط الجانبي -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 120px;">
                @if($document->fieldValues->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-list-ul me-2"></i>
                                معلومات الوثيقة
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($document->fieldValues as $fieldValue)
                                    <div class="list-group-item py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted small fw-semibold">
                                                <i class="fas fa-tag me-1"></i>
                                                {{ $fieldValue->field->label }}
                                            </div>
                                            <div class="ms-2">
                                                @switch($fieldValue->field->type)
                                                    @case('date')
                                                        <span class="fw-bold">
                                                            <i class="fas fa-calendar text-success ms-2"></i>
                                                            {{ \Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d') }}
                                                        </span>
                                                        @break
                                                    @case('number')
                                                        <span class="fw-bold">
                                                            <i class="fas fa-hashtag text-info ms-2"></i>
                                                            {{ number_format($fieldValue->value) }}
                                                        </span>
                                                        @break
                                                    @case('select')
                                                        @php
                                                            $baseUrl = ($document->section && $document->section->slug)
                                                                ? route('frontend.documents.section', [app()->getLocale(), $document->section->slug])
                                                                : route('frontend.documents.index');
                                                            $filterUrl = $baseUrl . '?' . http_build_query(['fields' => [ $fieldValue->field->id => $fieldValue->value ]]);
                                                        @endphp
                                                        <a href="{{ $filterUrl }}" class="badge bg-primary text-decoration-none" title="عرض كل الوثائق بنفس القيمة">
                                                            {{ $fieldValue->value }}
                                                        </a>
                                                        @break
                                                    @case('multiselect')
                                                        @php
                                                            $values = is_array($fieldValue->value) ? $fieldValue->value : (json_decode($fieldValue->value, true) ?: []);
                                                            $baseUrl = ($document->section && $document->section->slug)
                                                                ? route('frontend.documents.section', [app()->getLocale(), $document->section->slug])
                                                                : route('frontend.documents.index');
                                                        @endphp
                                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                                            @foreach($values as $value)
                                                                @php($filterUrl = $baseUrl . '?' . http_build_query(['fields' => [ $fieldValue->field->id => $value ]]))
                                                                <a href="{{ $filterUrl }}" class="badge bg-secondary text-decoration-none" title="عرض كل الوثائق بنفس القيمة">
                                                                    {{ $value }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                        @break
                                                    @case('file')
                                                        <a href="{{ asset('storage/' . $fieldValue->value) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-download me-2"></i>
                                                            تحميل الملف
                                                        </a>
                                                        @break
                                                    @case('textarea')
                                                        <div class="text-break" style="white-space: pre-wrap; line-height: 1.6;">{{ $fieldValue->value }}</div>
                                                        @break
                                                    @default
                                                        <span class="fw-bold text-dark">{{ $fieldValue->value }}</span>
                                                @endswitch
                                            </div>
                                        </div>
                                        @if($fieldValue->field->help_text)
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                {{ $fieldValue->field->help_text }}
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if($relatedDocuments->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-file-alt me-2"></i>
                                وثائق ذات صلة
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            @foreach($relatedDocuments as $related)
                                <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex">
                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px;">
                                            <i class="fas fa-file-alt text-muted"></i>
                                        </div>
                                        
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                @if($related->section && $related->section->slug)
                                                    <a href="{{ route('content.show', [app()->getLocale(), $related->section->name_en ?: $related->section->slug, $related->id]) }}" class="text-dark text-decoration-none">
                                                        {{ Str::limit($related->title, 50) }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">{{ Str::limit($related->title, 50) }}</span>
                                                @endif
                                            </h6>
                                            <div class="d-flex align-items-center text-muted small">
                                                <span class="me-2">
                                                    <i class="fas fa-eye me-1"></i>
                                                    {{ number_format($related->views_count) }}
                                                </span>
                                                <span>{{ $related->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// مشاركة الوثيقة
function shareDocument(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(@json($document->title));
    const description = encodeURIComponent(@json($document->excerpt ?: Str::limit(strip_tags($document->content), 100)));
    
    let shareUrl = '';
    
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${title}%20${url}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

// نسخ رابط الوثيقة
function copyDocumentLink(btn) {
    const doUIFeedback = (button, originalHtml) => {
        button.innerHTML = '<i class="fas fa-check text-success me-1"></i> تم النسخ';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    };

    const button = btn;
    const originalHtml = button.innerHTML;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(window.location.href)
            .then(() => doUIFeedback(button, originalHtml))
            .catch(() => {
                // Fallback
                const input = document.createElement('input');
                input.value = window.location.href;
                document.body.appendChild(input);
                input.select();
                try { document.execCommand('copy'); } catch (e) {}
                document.body.removeChild(input);
                doUIFeedback(button, originalHtml);
            });
    } else {
        // Fallback
        const input = document.createElement('input');
        input.value = window.location.href;
        document.body.appendChild(input);
        input.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(input);
        doUIFeedback(button, originalHtml);
    }
}

// طباعة الوثيقة
function printDocument() {
    window.print();
}

// DOM Ready بدون jQuery
document.addEventListener('DOMContentLoaded', function() {
    // تحسين تجربة القراءة
    document.querySelectorAll('.document-content img').forEach(img => {
        img.classList.add('img-fluid', 'rounded');
    });

    // إضافة تأثيرات للروابط
    document.querySelectorAll('.document-content a').forEach(a => {
        a.classList.add('text-decoration-none');
        a.addEventListener('mouseenter', () => a.classList.add('text-decoration-underline'));
        a.addEventListener('mouseleave', () => a.classList.remove('text-decoration-underline'));
    });

    // تحسين الجداول
    document.querySelectorAll('.document-content table').forEach(tbl => {
        tbl.classList.add('table', 'table-striped', 'table-responsive');
    });

    // إضافة smooth scrolling للروابط الداخلية
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({ top: target.offsetTop - 100, behavior: 'smooth' });
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.document-content {
    line-height: 1.8;
    font-size: 1.1rem;
    color: #333;
}
</style>
@endpush