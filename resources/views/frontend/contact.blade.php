@extends('frontend.layouts.app')

@section('title', __('messages.contact'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h2 text-center mb-4">{{ __('messages.contact') }}</h1>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
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

                        <div class="mb-3">
                            <label for="captcha_answer" class="form-label">{{ __('messages.captcha') }}: {{ $captchaQuestion ?? '' }}</label>
                            <input type="text" id="captcha_answer" name="captcha_answer" class="form-control @error('captcha_answer') is-invalid @enderror" value="{{ old('captcha_answer') }}" required>
                            @error('captcha_answer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">{{ __('messages.send') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
