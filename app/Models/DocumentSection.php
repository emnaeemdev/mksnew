<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'show_in_menu',
        'menu_order',
        'is_dropdown',
        'dropdown_title',
        'show_in_menu_ar',
        'show_in_menu_en',
        'menu_order_ar',
        'menu_order_en'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'show_in_menu' => 'boolean',
        'show_in_menu_ar' => 'boolean',
        'show_in_menu_en' => 'boolean',
        'menu_order' => 'integer',
        'menu_order_ar' => 'integer',
        'menu_order_en' => 'integer',
        'is_dropdown' => 'boolean'
    ];

    /**
     * Get the custom fields for this document section.
     */
    public function customFields()
    {
        return $this->hasMany(DocumentCustomField::class, 'section_id');
    }

    /**
     * Get the documents for this section.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'section_id');
    }

    /**
     * Get active custom fields ordered by sort order.
     */
    public function activeCustomFields()
    {
        return $this->customFields()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Get published documents count.
     */
    public function getPublishedDocumentsCountAttribute()
    {
        return $this->documents()->where('is_published', true)->count();
    }

    /**
     * Scope for active sections.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered sections.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}