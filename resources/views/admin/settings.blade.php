@extends('admin.layout')

@section('title', 'إعدادات النظام')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs me-2"></i>
                        إعدادات النظام
                    </h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">اسم الموقع <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                           id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required>
                                    @error('site_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">البريد الإلكتروني للتواصل <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                           id="contact_email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" required>
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="site_description" class="form-label">وصف الموقع</label>
                            <textarea class="form-control @error('site_description') is-invalid @enderror" 
                                      id="site_description" name="site_description" rows="3">{{ old('site_description', $settings['site_description']) }}</textarea>
                            @error('site_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">وصف مختصر للموقع يظهر في محركات البحث</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="footer_html">محتوى الفوتر (يدعم HTML)</label>
                            <textarea name="footer_html" id="footer_html" class="form-control" rows="6">{!! old('footer_html', $settings['footer_html'] ?? '') !!}</textarea>
                            <small class="form-text text-muted">سيتم عرض هذا المحتوى في أسفل جميع صفحات الموقع. يمكنك إضافة HTML مثل روابط، نص منسق، أو أكواد تتبع.</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="maintenance_mode">وضع الصيانة</label>
                        </div>
                        @error('maintenance_mode')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">عند التفعيل، سيتم إخفاء الموقع عن الزوار</small>

                        <hr>
                        <h5 class="mb-3">محتوى صفحة من نحن</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="about_html_ar" class="form-label">المحتوى (عربي)</label>
                                    <textarea name="about_html_ar" id="about_html_ar" class="form-control @error('about_html_ar') is-invalid @enderror" rows="6">{!! old('about_html_ar', $settings['about_html_ar'] ?? '') !!}</textarea>
                                    @error('about_html_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="about_html_en" class="form-label">المحتوى (إنجليزي)</label>
                                    <textarea name="about_html_en" id="about_html_en" class="form-control @error('about_html_en') is-invalid @enderror" rows="6">{!! old('about_html_en', $settings['about_html_en'] ?? '') !!}</textarea>
                                    @error('about_html_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">محتوى صفحة اتصل بنا</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_description_ar" class="form-label">الوصف (عربي)</label>
                                    <textarea name="contact_description_ar" id="contact_description_ar" class="form-control @error('contact_description_ar') is-invalid @enderror" rows="4">{!! old('contact_description_ar', $settings['contact_description_ar'] ?? '') !!}</textarea>
                                    @error('contact_description_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_description_en" class="form-label">الوصف (إنجليزي)</label>
                                    <textarea name="contact_description_en" id="contact_description_en" class="form-control @error('contact_description_en') is-invalid @enderror" rows="4">{!! old('contact_description_en', $settings['contact_description_en'] ?? '') !!}</textarea>
                                    @error('contact_description_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_address_ar" class="form-label">العنوان (عربي)</label>
                                    <input type="text" class="form-control @error('contact_address_ar') is-invalid @enderror" id="contact_address_ar" name="contact_address_ar" value="{{ old('contact_address_ar', $settings['contact_address_ar'] ?? '') }}">
                                    @error('contact_address_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_address_en" class="form-label">العنوان (إنجليزي)</label>
                                    <input type="text" class="form-control @error('contact_address_en') is-invalid @enderror" id="contact_address_en" name="contact_address_en" value="{{ old('contact_address_en', $settings['contact_address_en'] ?? '') }}">
                                    @error('contact_address_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">هاتف التواصل</label>
                                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}">
                                    @error('contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">إعدادات شريط/منبثق الاشتراك في النشرة</h5>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="newsletter_banner_enabled" name="newsletter_banner_enabled" value="1" {{ old('newsletter_banner_enabled', $settings['newsletter_banner_enabled'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="newsletter_banner_enabled">تفعيل شريط/منبثق الاشتراك</label>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="newsletter_banner_style" class="form-label">نمط العرض</label>
                                    <select id="newsletter_banner_style" name="newsletter_banner_style" class="form-select">
                                        @php $style = old('newsletter_banner_style', $settings['newsletter_banner_style'] ?? 'bottom'); @endphp
                                        <option value="top" {{ $style == 'top' ? 'selected' : '' }}>شريط أعلى الصفحة</option>
                                        <option value="bottom" {{ $style == 'bottom' ? 'selected' : '' }}>شريط أسفل الصفحة</option>
                                        <option value="side" {{ $style == 'side' ? 'selected' : '' }}>لوحة جانبية</option>
                                        <option value="modal" {{ $style == 'modal' ? 'selected' : '' }}>منبثق يغطي الصفحة</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="newsletter_banner_delay" class="form-label">تأخير الظهور (ثواني)</label>
                                    <input type="number" min="0" max="120" class="form-control" id="newsletter_banner_delay" name="newsletter_banner_delay" value="{{ old('newsletter_banner_delay', $settings['newsletter_banner_delay'] ?? 1) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="newsletter_banner_cooldown_days" class="form-label">مدة الإخفاء بعد الإغلاق (أيام)</label>
                                    <input type="number" min="1" max="365" class="form-control" id="newsletter_banner_cooldown_days" name="newsletter_banner_cooldown_days" value="{{ old('newsletter_banner_cooldown_days', $settings['newsletter_banner_cooldown_days'] ?? 30) }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newsletter_banner_title_ar" class="form-label">العنوان (عربي)</label>
                                    <input type="text" class="form-control" id="newsletter_banner_title_ar" name="newsletter_banner_title_ar" value="{{ old('newsletter_banner_title_ar', $settings['newsletter_banner_title_ar'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newsletter_banner_title_en" class="form-label">العنوان (إنجليزي)</label>
                                    <input type="text" class="form-control" id="newsletter_banner_title_en" name="newsletter_banner_title_en" value="{{ old('newsletter_banner_title_en', $settings['newsletter_banner_title_en'] ?? '') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newsletter_banner_text_ar" class="form-label">النص (عربي)</label>
                                    <textarea class="form-control" id="newsletter_banner_text_ar" name="newsletter_banner_text_ar" rows="3">{{ old('newsletter_banner_text_ar', $settings['newsletter_banner_text_ar'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newsletter_banner_text_en" class="form-label">النص (إنجليزي)</label>
                                    <textarea class="form-control" id="newsletter_banner_text_en" name="newsletter_banner_text_en" rows="3">{{ old('newsletter_banner_text_en', $settings['newsletter_banner_text_en'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="newsletter_banner_bg_color" class="form-label">لون الخلفية</label>
                                    <input type="color" class="form-control form-control-color" id="newsletter_banner_bg_color" name="newsletter_banner_bg_color" value="{{ old('newsletter_banner_bg_color', $settings['newsletter_banner_bg_color'] ?? '#111827') }}" title="اختر لون الخلفية">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="newsletter_banner_text_color" class="form-label">لون النص</label>
                                    <input type="color" class="form-control form-control-color" id="newsletter_banner_text_color" name="newsletter_banner_text_color" value="{{ old('newsletter_banner_text_color', $settings['newsletter_banner_text_color'] ?? '#ffffff') }}" title="اختر لون النص">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="newsletter_banner_button_color" class="form-label">لون زر الاشتراك</label>
                                    <input type="color" class="form-control form-control-color" id="newsletter_banner_button_color" name="newsletter_banner_button_color" value="{{ old('newsletter_banner_button_color', $settings['newsletter_banner_button_color'] ?? '#2563eb') }}" title="اختر لون الزر">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">معلومات النظام</h5>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-code"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">إصدار Laravel</span>
                                        <span class="info-box-number">{{ app()->version() }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fab fa-php"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">إصدار PHP</span>
                                        <span class="info-box-number">{{ PHP_VERSION }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-database"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">قاعدة البيانات</span>
                                        <span class="info-box-number">{{ config('database.default') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger">
                                        <i class="fas fa-server"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">البيئة</span>
                                        <span class="info-box-number">{{ config('app.env') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-1"></i>
                                العودة للوحة التحكم
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.info-box {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box-icon {
    border-top-left-radius: 2px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 2px;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 45px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 90px;
}

.info-box-text {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 13px;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}
</style>
@endsection

@section('scripts')
<script src="{{ asset('dashboard/tinymce/tinymce.min.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const arabicSelectors = ['#about_html_ar', '#contact_description_ar'];
    const englishSelectors = ['#about_html_en', '#contact_description_en'];
    const commonSelectors = ['#footer_html'];

    // RTL editors (Arabic)
    tinymce.init({
      selector: arabicSelectors.concat(commonSelectors).join(','),
      directionality: 'rtl',
      menubar: false,
      plugins: 'link lists code table',
      toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code',
      height: 280,
      branding: false,
      content_style: 'body { font-family: Cairo, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, \"Apple Color Emoji\", \"Segoe UI Emoji\"; }'
    });

    // LTR editors (English)
    tinymce.init({
      selector: englishSelectors.join(','),
      directionality: 'ltr',
      menubar: false,
      plugins: 'link lists code table',
      toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code',
      height: 280,
      branding: false
    });
  });
</script>
@endsection