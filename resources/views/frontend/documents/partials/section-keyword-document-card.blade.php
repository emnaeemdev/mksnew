@php
    $document = $document ?? null;
    if (!$document) return;

    $locale = app()->getLocale();
    $sectionSlug = optional($document->section)->slug ?: ($section->slug ?? null);
    $documentShowUrl = $sectionSlug
        ? route('content.show', [$locale, $sectionSlug, $document->id])
        : route('frontend.documents.show', [$locale, $document]);

    $displayFields = collect($document->plainFieldValues ?? [])
        ->filter(function ($fieldValue) {
            if (!$fieldValue->field || $fieldValue->value === null || trim((string) $fieldValue->value) === '') {
                return false;
            }

            return in_array($fieldValue->field->type, ['text', 'textarea', 'select', 'radio', 'number', 'date', 'datetime'], true);
        })
        ->take(8);
@endphp

<a href="{{ $documentShowUrl }}" class="text-decoration-none section-kw-doc-link">
    <article class="section-kw-doc-card">
        @if($document->section)
            <div class="section-kw-doc-section">
                <i class="fas fa-folder-open me-1"></i>
                {{ app()->getLocale() === 'ar' ? $document->section->name : ($document->section->name_en ?: $document->section->name) }}
            </div>
        @endif

        <h3 class="section-kw-doc-title">{{ $document->title }}</h3>

        @if($displayFields->isNotEmpty())
            <dl class="section-kw-doc-fields">
                @foreach($displayFields as $fieldValue)
                    <div class="section-kw-doc-field">
                        <dt>{{ $fieldValue->field->label }}</dt>
                        <dd>
                            @if(in_array($fieldValue->field->type, ['date', 'datetime'], true))
                                {{ \Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d') }}
                            @else
                                {{ Str::limit(strip_tags((string) $fieldValue->value), 120) }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        @elseif($document->excerpt)
            <p class="section-kw-doc-excerpt">{{ Str::limit(strip_tags($document->excerpt), 180) }}</p>
        @endif

        <div class="section-kw-doc-meta">
            @if($document->published_at)
                <span><i class="fas fa-calendar-alt me-1"></i>{{ $document->published_at->format('Y-m-d') }}</span>
            @endif
            <span><i class="fas fa-eye me-1"></i>{{ number_format($document->views_count) }}</span>
        </div>
    </article>
</a>
