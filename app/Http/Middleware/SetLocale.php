<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }

    public static function resolveFromRequest(Request $request): string
    {
        return (new self())->resolveLocale($request);
    }

    private function resolveLocale(Request $request): string
    {
        $routeLocale = $request->route()?->parameter('locale');
        if (is_string($routeLocale) && in_array($routeLocale, ['ar', 'en'], true)) {
            return $routeLocale;
        }

        $path = trim($request->path(), '/');
        if (preg_match('/^(ar|en)(?:\/|$)/', $path, $matches)) {
            return $matches[1];
        }

        $sessionLocale = session('locale');
        if (is_string($sessionLocale) && in_array($sessionLocale, ['ar', 'en'], true)) {
            return $sessionLocale;
        }

        return 'ar';
    }
}
