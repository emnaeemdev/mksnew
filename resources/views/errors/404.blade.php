@extends('frontend.layouts.app')

@section('title', __('messages.not_found_title'))

@section('content')
<div class="error-page">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="error-page-card text-center">
                    <div class="error-page-code" aria-hidden="true">{{ __('messages.not_found_code') }}</div>
                    <h1 class="error-page-title">{{ __('messages.not_found_title') }}</h1>
                    <p class="error-page-message">{{ __('messages.not_found_message') }}</p>
                    <a href="{{ route('home', app()->getLocale()) }}" class="btn error-page-btn">
                        <i class="fas fa-home ms-2"></i>
                        {{ __('messages.back_home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.error-page {
    min-height: 55vh;
    display: flex;
    align-items: center;
}

.error-page-card {
    background: #fff;
    border: 2px solid var(--brand-blue, #2b4596);
    border-radius: 16px;
    padding: 3rem 2rem;
    box-shadow: 0 12px 30px rgba(43, 69, 150, 0.08);
}

.error-page-code {
    font-size: clamp(4rem, 12vw, 7rem);
    font-weight: 700;
    line-height: 1;
    color: var(--brand-orange, #e9640a);
    margin-bottom: 1rem;
}

.error-page-title {
    font-size: clamp(1.5rem, 4vw, 2rem);
    font-weight: 700;
    color: var(--brand-blue, #2b4596);
    margin-bottom: 1rem;
}

.error-page-message {
    font-size: 1.1rem;
    color: #5c6578;
    margin-bottom: 2rem;
}

.error-page-btn {
    background-color: var(--brand-blue, #2b4596);
    border-color: var(--brand-blue, #2b4596);
    color: #fff;
    padding: 0.65rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
}

.error-page-btn:hover,
.error-page-btn:focus {
    background-color: #1f2f6b;
    border-color: #1f2f6b;
    color: #fff;
}
</style>
@endpush
