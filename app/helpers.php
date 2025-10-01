<?php

use App\Models\Setting as SettingModel;

if (!function_exists('setting')) {
    /**
     * Get setting value by key with optional default
     */
    function setting(string $key, $default = null)
    {
        return SettingModel::get($key, $default);
    }
}