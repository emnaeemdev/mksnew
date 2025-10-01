@extends('frontend.layouts.app')

@section('title', __('messages.privacy_policy'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="display-4 text-center mb-4">{{ __('messages.privacy_policy') }}</h1>
                    
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt text-white fa-2x"></i>
                        </div>
                    </div>
                    
                    <div class="content">
                        <p class="lead text-muted text-center mb-4">
                            {{ __('messages.privacy_description') }}
                        </p>
                        
                        <div class="privacy-content">
                            <h4>{{ __('messages.information_collection') }}</h4>
                            <p class="text-muted mb-4">{{ __('messages.information_collection_text') }}</p>
                            
                            <h4>{{ __('messages.information_usage') }}</h4>
                            <p class="text-muted mb-4">{{ __('messages.information_usage_text') }}</p>
                            
                            <h4>{{ __('messages.data_protection') }}</h4>
                            <p class="text-muted mb-4">{{ __('messages.data_protection_text') }}</p>
                            
                            <h4>{{ __('messages.contact_privacy') }}</h4>
                            <p class="text-muted mb-4">{{ __('messages.contact_privacy_text') }}</p>
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