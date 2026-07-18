<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Models\DocumentSection;
use App\Models\Document;
use App\Models\Post;
use App\Models\Podcast;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
                $adminPrefix = trim((string) config('admin.path', 'panel'), '/');
                // إذا كان الطلب من لوحة التحكم، استخدم ID فقط
                if (request()->is($adminPrefix . '/*')) {
                    return Document::with('files')->where('id', $value)->firstOrFail();
                }
                // للواجهة الأمامية، استخدم slug أو ID
                return Document::with(['section', 'fieldValues.field', 'files'])
                    ->where(function ($query) use ($value) {
                        $query->where('slug', $value)
                            ->orWhere('id', $value);
                    })
                    ->firstOrFail();
            });

            // ربط مخصص للمقالات
            Route::bind('post', function ($value) {
                $adminPrefix = trim((string) config('admin.path', 'panel'), '/');
                // إذا كان الطلب من لوحة التحكم، استخدم ID فقط
                if (request()->is($adminPrefix . '/*')) {
                    return Post::where('id', $value)->firstOrFail();
                }
                // للواجهة الأمامية، استخدم slug أو ID
                return Post::where('slug', $value)
                    ->orWhere('id', $value)
                    ->firstOrFail();
            });

            // ربط مخصص للبودكاست: في الواجهة الأمامية يدعم المعرّف أو السلغ، وفي لوحة التحكم يعتمد على المعرّف فقط
            Route::bind('podcast', function ($value) {
                $adminPrefix = trim((string) config('admin.path', 'panel'), '/');
                if (request()->is($adminPrefix . '/*')) {
                    return Podcast::where('id', $value)->firstOrFail();
                }
                return Podcast::where('id', $value)
                    ->orWhere('slug', $value)
                    ->firstOrFail();
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function () {
            $adminPath = trim((string) config('admin.path', 'panel'), '/');

            return "/{$adminPath}/login";
        });

        $middleware->alias([
            'setlocale' => \App\Http\Middleware\SetLocale::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                app()->setLocale(\App\Http\Middleware\SetLocale::resolveFromRequest($request));

                return response()->json([
                    'message' => __('messages.not_found_message'),
                ], 404);
            }

            app()->setLocale(\App\Http\Middleware\SetLocale::resolveFromRequest($request));

            return response()->view('errors.404', [], 404);
        });
    })->create();
