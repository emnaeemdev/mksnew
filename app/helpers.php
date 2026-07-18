<?php

use App\Models\Setting as SettingModel;

if (!function_exists('setting')) {

    function setting(string $key, $default = null)
    {
        return SettingModel::get($key, $default);
    }
}

if (!function_exists('safe_html')) {
    /**
     * Sanitize rich HTML before unescaped output.
     */
    function safe_html(?string $html): string
    {
        return app(\App\Services\HtmlSanitizer::class)->clean($html);
    }
}