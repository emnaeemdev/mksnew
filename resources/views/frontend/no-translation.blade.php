@extends('frontend.layouts.app')

@section('title', __('messages.no_translation_title') )

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h1 class="display-6 mb-3">{{ __('messages.no_translation_title') }}</h1>
                    <p class="lead mb-4">{{ __('messages.no_translation_message') }}</p>
                    <a href="{{ url(app()->getLocale() === 'en' ? '/en' : '/ar') }}" class="btn btn-primary">
                        {{ __('messages.back_home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection