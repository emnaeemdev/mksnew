<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{

    public function switch($locale)
    {

        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'ar';
        }

        Session::put('locale', $locale);

        app()->setLocale($locale);

        $currentUrl = url()->previous();
        $currentPath = parse_url($currentUrl, PHP_URL_PATH);

        $pathWithoutLang = preg_replace('/^\/(ar|en)/', '', $currentPath);

        $newPath = '/' . $locale . $pathWithoutLang;

        if ($newPath === '/' . $locale) {
            $newPath = '/' . $locale;
        }

        return redirect($newPath);
    }
}