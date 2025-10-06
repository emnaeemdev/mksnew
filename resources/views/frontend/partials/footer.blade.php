<footer class="footer text-white py-5 mt-5">
    <div class="container">
        @if(!empty($footerHtml))
            <div class="mb-4">
                {!! $footerHtml !!}
            </div>
            <hr class="my-4">
        @endif

        <div class="row g-4"><!-- g-4 يضيف مسافات متساوية بين الأعمدة -->
            <!-- العمود 1 -->
            <div class="col-md-3 d-flex flex-column text-center">
                <h5 class="fw-bold mb-3">
                    
                    
                                         @if(app()->getLocale() == 'ar')
                    <img alt="Logo" src="{{ asset('images/logo-arabic-header_mks.png') }}" class="footer_logo" style="width: 60%;" />
                        {{-- <h6 class="fw-bold mb-3">الذاكرة والمعرفة للدراسات</h6> --}}
                    @else
                    <img alt="Logo" src="{{ asset('images/logo_header_en_mks.png') }}" class="footer_logo" style="width: 60%;" />
                        {{-- <h6 class="fw-bold mb-3">Memory and Knowledge Studies</h6> --}}
                    @endif
                </h5>

            </div>

            <!-- العمود 2 -->
            <div class="col-md-3 d-flex flex-column text-center">
                     @if(app()->getLocale() == 'ar')
                        <h6 class="fw-bold mb-3">تابعنا</h6>
                    @else
                        <h6 class="fw-bold mb-3">Follow Us</h6>
                    @endif
                
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="#" class="text-white" title="فيسبوك"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-white" title="تويتر"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white" title="إنستغرام"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white" title="يوتيوب"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>

            <!-- العمود 3 -->
            <div class="col-md-3 d-flex flex-column text-center">
                <h6 class="fw-bold mb-3">{{ __('messages.newsletter_heading') }}</h6>
                <p class="text-muted small mb-3">
                    {{-- {{ __('messages.newsletter_description') }} --}}
                </p>

                @if (session('success'))
                    <div class="alert alert-success py-2 px-3 small">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger py-2 px-3 small">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('newsletter.subscribe', [app()->getLocale()]) }}">
                    @csrf
                    <div class="mb-2">
                        <input type="text" class="form-control form-control-sm" name="name" value="{{ old('name') }}" placeholder="{{ __('messages.newsletter_name') }}">
                    </div>
                    <div class="input-group input-group-sm mb-2">
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="{{ __('messages.newsletter_email') }}" required>
                        <button class="btn btn-primary" type="submit" aria-label="{{ __('messages.newsletter_subscribe') }}">
                            <i class="fas fa-paper-plane me-1"></i>
                            <span class="d-none d-sm-inline">{{ __('messages.newsletter_subscribe') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- العمود 4 -->
            <div class="col-md-3 d-flex flex-column text-center">
                                     @if(app()->getLocale() == 'ar')
                                        <p class="small mb-1">محتوى الموقع منشور برخصة المشاع الإبداعي</p>
                <p class="small mb-0">نَسب المُصنَّف 4.0</p>
                    @else
                                        <p class="small mb-1">censed under a Creative Commons license Attribution-ShareAlike</p>
                <p class="small mb-0"> 4.0 International (CC BY-SA 4.0)</p>
                    @endif

            </div>
        </div>

    </div>
</footer>
