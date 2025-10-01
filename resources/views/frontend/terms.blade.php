@extends('frontend.layouts.app')

@section('title', __('messages.terms_of_service'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="display-4 text-center mb-4">{{ __('messages.terms_of_service') }}</h1>
                    
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-file-contract text-white fa-2x"></i>
                        </div>
                    </div>
                    
                    <div class="content">
                        <p class="lead text-muted text-center mb-4">
                            {{ __('messages.terms_description') }}
                        </p>
                        
                        <div class="terms-content">
                            <h4>{{ __('messages.acceptance_terms') }}</h4>
                            <p class="text-muted mb-4">{{ __('messages.acceptance_terms_text') }}</p>
                            
                            <h4>{{ __('messages.use_license') }}</h4>
                            <p class="text-muted mb-4">{{ __('messages.use_license_text') }}</p>
                            
                            <h4>{{ __('messages.disclaimer') }}</h4>
                            <p class="text-muted mb-4">{{ __('messages.disclaimer_text') }}</p>
                            
                            <h4>{{ __('messages.limitations') }}</h4>
                            <p class="text-muted mb-4">{{ __('messages.limitations_text') }}</p>
                            
                            <h4>{{ __('messages.modifications') }}</h4>
                            <p class="text-muted mb-4">{{ __('messages.modifications_text') }}</p>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="{{ route('posts.index') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                {{ __('messages.back_to_posts') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection