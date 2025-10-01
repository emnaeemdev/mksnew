@extends('frontend.layouts.app')

@section('title', __('messages.sitemap'))

@section('content')
<div class="container py-5">
    <h1 class="mb-4">{{ __('messages.sitemap') }}</h1>
    <ul class="list-unstyled">
        <li class="mb-2">
            <a href="{{ route('about', app()->getLocale()) }}" class="text-decoration-none">
                {{ __('messages.about') }}
            </a>
        </li>
        <li class="mb-2">
            <a href="{{ route('contact', app()->getLocale()) }}" class="text-decoration-none">
                {{ __('messages.contact') }}
            </a>
        </li>
        {{-- Keep other links as they are --}}
    </ul>
</div>
@endsection