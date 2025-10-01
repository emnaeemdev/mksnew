@extends('frontend.layouts.app')

@section('title', __('messages.contact'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="display-4 text-center mb-4">{{ __('messages.contact') }}</h1>
                    
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-envelope text-white fa-2x"></i>
                        </div>
                    </div>
                    
                    @php
                        $locale = app()->getLocale();
                        $descKey = $locale === 'ar' ? 'contact_description_ar' : 'contact_description_en';
                        $addressKey = $locale === 'ar' ? 'contact_address_ar' : 'contact_address_en';
                        $description = setting($descKey);
                        $address = setting($addressKey);
                        $phone = setting('contact_phone');
                    @endphp

                    <div class="row g-5">
                        <div class="col-lg-6">
                            <div class="content mb-4">
                                @if(!empty($description))
                                    <p class="lead text-muted">{!! $description !!}</p>
                                @else
                                    <p class="lead text-muted">{{ __('messages.contact_description') }}</p>
                                @endif
                            </div>

                            <div class="contact-info">
                                <div class="mb-3 d-flex align-items-start">
                                    <div class="me-3"><i class="fas fa-map-marker-alt text-primary fa-lg"></i></div>
                                    <div>
                                        <h6 class="mb-1">{{ __('messages.address') }}</h6>
                                        <p class="mb-0 text-muted">{{ $address ?? __('messages.our_address') }}</p>
                                    </div>
                                </div>
                                <div class="mb-3 d-flex align-items-start">
                                    <div class="me-3"><i class="fas fa-phone text-primary fa-lg"></i></div>
                                    <div>
                                        <h6 class="mb-1">{{ __('messages.phone') }}</h6>
                                        <p class="mb-0 text-muted">{{ $phone ?? __('messages.our_phone') }}</p>
                                    </div>
                                </div>
                                <div class="mb-3 d-flex align-items-start">
                                    <div class="me-3"><i class="fas fa-envelope text-primary fa-lg"></i></div>
                                    <div>
                                        <h6 class="mb-1">{{ __('messages.email') }}</h6>
                                        <p class="mb-0 text-muted">{{ setting('contact_email') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('contact.submit', app()->getLocale()) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('messages.name') }}</label>
                                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('messages.email') }}</label>
                                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">{{ __('messages.phone') }}</label>
                                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">{{ __('messages.subject') }}</label>
                                    <input type="text" id="subject" name="subject" class="form-control" value="{{ old('subject') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">{{ __('messages.message') }}</label>
                                    <textarea id="message" name="message" rows="5" class="form-control" required>{{ old('message') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">{{ __('messages.send') }}</button>
                            </form>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('posts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ __('messages.back_to_posts') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection