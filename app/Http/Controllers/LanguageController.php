<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     *
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch($locale)
    {
        // Validate the locale
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'ar'; // Default to Arabic
        }
        
        // Store the locale in session
        Session::put('locale', $locale);
        
        // Set the application locale
        app()->setLocale($locale);
        
        // Get current URL and replace language prefix
        $currentUrl = url()->previous();
        $currentPath = parse_url($currentUrl, PHP_URL_PATH);
        
        // Remove existing language prefix if present
        $pathWithoutLang = preg_replace('/^\/(ar|en)/', '', $currentPath);
        
        // Add new language prefix
        $newPath = '/' . $locale . $pathWithoutLang;
        
        // If path is just the language, redirect to home
        if ($newPath === '/' . $locale) {
            $newPath = '/' . $locale;
        }
        
        return redirect($newPath);
    }
}