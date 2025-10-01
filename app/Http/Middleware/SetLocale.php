<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        
        // If no locale in URL, use session or default to Arabic
        if (!$locale) {
            $locale = session('locale', 'ar');
        }
        
        // Validate locale
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'ar';
        }
        
        // Set application locale
        app()->setLocale($locale);
        session(['locale' => $locale]);
        
        return $next($request);
    }
}
