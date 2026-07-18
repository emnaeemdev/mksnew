<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    protected $casts = [
        'value' => 'string'
    ];

    public static function get($key, $default = null)
    {
        $setting = Cache::remember("setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );

        Cache::forget("setting_{$key}");

        return $setting;
    }

    protected static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    public static function clearCache()
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
        }
    }
}
