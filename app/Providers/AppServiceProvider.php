<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\SidebarComposer;
use Illuminate\Pagination\Paginator; // added
use App\Models\Setting; // added

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تسجيل View Composer للقائمة الجانبية
        View::composer('admin.partials.sidebar', SidebarComposer::class);

        // استخدام قوالب Bootstrap 5 الافتراضية للترقيم في جميع الواجهات
        Paginator::useBootstrapFive();
        // يمكن تخصيص القالب الافتراضي لاحقًا إن لزم: Paginator::defaultView('vendor.pagination.bootstrap-5');

        // مشاركة إعدادات عامة على كل الواجهات وتحديث اسم التطبيق من الإعدادات
        $siteName = Setting::get('site_name', config('app.name', 'MK Snow'));
        config(['app.name' => $siteName]);
        $footerHtml = Setting::get('footer_html', '');
        View::share('siteName', $siteName);
        View::share('footerHtml', $footerHtml);
    }
}
