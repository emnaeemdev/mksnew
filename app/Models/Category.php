<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'description_ar',
        'description_en',
        'is_active',
        'sort_order',
        'show_in_menu',
        'menu_order',
        'show_in_menu_ar',
        'show_in_menu_en',
        'menu_order_ar',
        'menu_order_en'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_menu_ar' => 'boolean',
        'show_in_menu_en' => 'boolean',
        'menu_order' => 'integer',
        'menu_order_ar' => 'integer',
        'menu_order_en' => 'integer',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function activePosts(): HasMany
    {
        return $this->hasMany(Post::class)->where('status', 'published');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name_en);
            }
        });
    }

    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }
}
