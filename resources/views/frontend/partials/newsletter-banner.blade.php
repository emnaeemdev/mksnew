@if(!empty($newsletterBanner['enabled']))
@php
    $locale = app()->getLocale();
    $title = $locale === 'ar' ? ($newsletterBanner['title_ar'] ?: 'اشترك في نشرتنا') : ($newsletterBanner['title_en'] ?: 'Subscribe to our Newsletter');
    $text = $locale === 'ar' ? ($newsletterBanner['text_ar'] ?: '') : ($newsletterBanner['text_en'] ?: '');
    $style = $newsletterBanner['style'] ?? 'modal';
    $bgColor = $newsletterBanner['bg_color'] ?? '#111827';
    $textColor = $newsletterBanner['text_color'] ?? '#ffffff';
    $btnColor = $newsletterBanner['button_color'] ?? '#2563eb';
    $nlCooldownDays = (int) ($newsletterBanner['cooldown_days'] ?? 30);
@endphp
<script>
(function () {
    try {
        var raw = localStorage.getItem('mks_newsletter_banner_dismissed');
        if (!raw) return;
        var dismissedAt = parseInt(raw, 10);
        if (isNaN(dismissedAt)) return;
        var cooldownMs = {{ $nlCooldownDays }} * 24 * 60 * 60 * 1000;
        if (Date.now() - dismissedAt < cooldownMs) {
            document.documentElement.classList.add('newsletter-banner-suppressed');
        }
    } catch (e) {}
})();
</script>
<style>html.newsletter-banner-suppressed #newsletter-banner,html.newsletter-banner-suppressed #newsletter-banner-overlay{display:none!important;visibility:hidden!important}</style>

@if($style === 'modal')
<div id="newsletter-banner-overlay"></div>
@endif

<div id="newsletter-banner"
     class="newsletter-banner newsletter-banner--{{ $style }}"
     data-style="{{ $style }}"
     data-delay="{{ $newsletterBanner['delay'] ?? 1 }}"
     data-cooldown="{{ $newsletterBanner['cooldown_days'] ?? 30 }}"
     data-error="{{ __('messages.newsletter_error') }}">
    <div class="newsletter-banner__card" style="background: linear-gradient(145deg, {{ $bgColor }}ee 0%, {{ $bgColor }} 100%); color: {{ $textColor }};">
        <button type="button" class="newsletter-banner__close" data-newsletter-close aria-label="إغلاق">
            <i class="fas fa-times"></i>
        </button>

        <div class="newsletter-banner__icon">
            <i class="fas fa-envelope-open-text"></i>
        </div>

        <h3 class="newsletter-banner__title">{{ $title }}</h3>

        @if($text)
            <p class="newsletter-banner__text">{{ $text }}</p>
        @endif

        <form class="newsletter-banner__form"
              method="POST"
              action="{{ route('newsletter.subscribe', [$locale]) }}">
            @csrf
            <input type="text"
                   name="name"
                   class="form-control"
                   placeholder="{{ __('messages.newsletter_name') }}"
                   autocomplete="name">
            <input type="email"
                   name="email"
                   class="form-control"
                   placeholder="{{ __('messages.newsletter_email') }}"
                   required
                   autocomplete="email">
            <button type="submit"
                    class="newsletter-banner__submit"
                    style="background: {{ $btnColor }}; color: #fff;">
                <i class="fas fa-paper-plane"></i>
                {{ __('messages.newsletter_subscribe') }}
            </button>
        </form>

        <div class="newsletter-banner__message"></div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/newsletter-banner.css') }}">
<script src="{{ asset('js/newsletter-banner.js') }}" defer></script>
@endif
