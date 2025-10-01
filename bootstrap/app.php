<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Models\DocumentSection;
use App\Models\Document;
use App\Models\Post;
use App\Models\Podcast;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Configure route model binding
            // استبدال Route::model بربط مخصص يعتمد على slug
            Route::bind('section', function ($value) {
                return DocumentSection::where('slug', $value)->firstOrFail();
            });

            // ربط مخصص للوثيقة أيضاً بالـ slug أو الـ id
            Route::bind('document', function ($value) {
                // إذا كان الطلب من لوحة التحكم، استخدم ID فقط
                if (request()->is('admin/*')) {
                    return Document::where('id', $value)->firstOrFail();
                }
                // للواجهة الأمامية، استخدم slug أو ID
                return Document::where('slug', $value)
                    ->orWhere('id', $value)
                    ->firstOrFail();
            });

            // ربط مخصص للمقالات
            Route::bind('post', function ($value) {
                // إذا كان الطلب من لوحة التحكم، استخدم ID فقط
                if (request()->is('admin/*')) {
                    return Post::where('id', $value)->firstOrFail();
                }
                // للواجهة الأمامية، استخدم slug أو ID
                return Post::where('slug', $value)
                    ->orWhere('id', $value)
                    ->firstOrFail();
            });

            // ربط مخصص للبودكاست: في الواجهة الأمامية يدعم المعرّف أو السلغ، وفي لوحة التحكم يعتمد على المعرّف فقط
            Route::bind('podcast', function ($value) {
                if (request()->is('admin/*')) {
                    return Podcast::where('id', $value)->firstOrFail();
                }
                return Podcast::where('id', $value)
                    ->orWhere('slug', $value)
                    ->firstOrFail();
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'setlocale' => \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
