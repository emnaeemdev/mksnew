<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\ConnectionEstablished;
use App\Http\View\Composers\SidebarComposer;
use App\Http\View\Composers\FrontendAdminShortcutComposer;
use Illuminate\Pagination\Paginator; // added
use App\Models\Setting; // added
use App\Models\Document;
use App\Models\DocumentFieldValue;
use App\Observers\DocumentObserver;
use App\Observers\DocumentFieldValueObserver;
use Throwable;

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
        // قبل أي استعلام: تسجيل حماية انقطاع MySQL
        $this->registerMysqlIdleReconnect();

        // تسجيل View Composer للقائمة الجانبية
        View::composer('admin.partials.sidebar', SidebarComposer::class);
        View::composer('frontend.partials.header', FrontendAdminShortcutComposer::class);

        // استخدام قوالب Bootstrap 5 الافتراضية للترقيم في جميع الواجهات
        Paginator::useBootstrapFive();
        // يمكن تخصيص القالب الافتراضي لاحقًا إن لزم: Paginator::defaultView('vendor.pagination.bootstrap-5');

        // مشاركة إعدادات عامة على كل الواجهات وتحديث اسم التطبيق من الإعدادات
        $siteName = Setting::get('site_name', config('app.name', 'MK Snow'));
        config(['app.name' => $siteName]);
        $footerHtml = Setting::get('footer_html', '');
        View::share('siteName', $siteName);
        View::share('footerHtml', $footerHtml);

        View::share('newsletterBanner', [
            'enabled' => (bool) Setting::get('newsletter_banner_enabled', false),
            'style' => Setting::get('newsletter_banner_style', 'modal'),
            'title_ar' => Setting::get('newsletter_banner_title_ar', 'اشترك في نشرتنا'),
            'title_en' => Setting::get('newsletter_banner_title_en', 'Subscribe to our Newsletter'),
            'text_ar' => Setting::get('newsletter_banner_text_ar', ''),
            'text_en' => Setting::get('newsletter_banner_text_en', ''),
            'bg_color' => Setting::get('newsletter_banner_bg_color', '#111827'),
            'text_color' => Setting::get('newsletter_banner_text_color', '#ffffff'),
            'button_color' => Setting::get('newsletter_banner_button_color', '#2563eb'),
            'delay' => (int) Setting::get('newsletter_banner_delay', 1),
            'cooldown_days' => (int) Setting::get('newsletter_banner_cooldown_days', 30),
        ]);

        Document::observe(DocumentObserver::class);
        DocumentFieldValue::observe(DocumentFieldValueObserver::class);
    }

    /**
     * إذا بقي اتصال MySQL خاملًا ثم انقطع، أعد الاتصال قبل الاستعلام التالي
     * بدل بدء بحث ثقيل على اتصال ميت.
     */
    protected function registerMysqlIdleReconnect(): void
    {
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
            $connection = $event->connection;

            if (!$connection instanceof Connection) {
                return;
            }

            if (!in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
                return;
            }

            static $registered = [];
            $name = $connection->getName();
            if (isset($registered[$name])) {
                return;
            }
            $registered[$name] = true;

            $connection->beforeExecuting(function ($query, $bindings, Connection $connection) {
                static $lastActivity = [];
                $name = $connection->getName();
                $now = microtime(true);
                $idleFor = isset($lastActivity[$name]) ? ($now - $lastActivity[$name]) : 0.0;

                // أقل من wait_timeout الشائع على الاستضافات المشتركة (غالبًا 60 ثانية)
                if ($idleFor >= 25) {
                    try {
                        $pdo = $connection->getPdo();
                        if ($pdo) {
                            $pdo->query('SELECT 1');
                        }
                    } catch (Throwable $e) {
                        try {
                            $connection->reconnect();
                        } catch (Throwable $reconnectError) {
                            // سيُعاد رمي الخطأ من الاستعلام الأصلي إن لزم
                        }
                    }
                }

                $lastActivity[$name] = $now;
            });
        });
    }
}
