@extends('frontend.layouts.app')

@section('title', __('messages.about'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="display-4 text-center mb-4">{{ __('messages.about') }}</h1>
                    
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-info-circle text-white fa-2x"></i>
                        </div>
                    </div>
                    
                    @php
                        $locale = app()->getLocale();
                        $aboutKey = $locale === 'ar' ? 'about_html_ar' : 'about_html_en';
                        $aboutHtml = setting($aboutKey);
                    @endphp

                    @if(!empty($aboutHtml))
                        <div class="content">
                            {!! $aboutHtml !!}
                        </div>
                    @else
                        <div class="content">
                            <p class="lead text-muted text-center mb-4">
                                {{ __('messages.about_description') }}
                            </p>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="feature-box text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="fas fa-rocket text-primary fa-2x"></i>
                                        </div>
                                        <h5>{{ __('messages.our_mission') }}</h5>
                                        <p class="text-muted">{{ __('messages.mission_description') }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="feature-box text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="fas fa-eye text-primary fa-2x"></i>
                                        </div>
                                        <h5>{{ __('messages.our_vision') }}</h5>
                                        <p class="text-muted">{{ __('messages.vision_description') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    

                </div>
            </div>
        </div>
    </div>
</div>
@endsection