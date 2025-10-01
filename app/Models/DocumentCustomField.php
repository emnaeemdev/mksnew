<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentCustomField extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'name',
        'label',
        'type',
        'options',
        'is_required',
        'is_active',
        'sort_order',
        'validation_rules',
        'placeholder',
        'help_text'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    const FIELD_TYPES = [
        'text' => 'نص قصير',
        'textarea' => 'نص طويل',
        'number' => 'رقم',
        'date' => 'تاريخ',
        'datetime' => 'تاريخ ووقت',
        'select' => 'قائمة منسدلة',
        'checkbox' => 'اختيارات متعددة',
        'radio' => 'اختيار واحد',
        'email' => 'بريد إلكتروني',
        'url' => 'رابط',
        'file' => 'ملف',
        'image' => 'صورة'
    ];

    /**
     * Get the document section that owns this custom field.
     */
    public function section()
    {
        return $this->belongsTo(DocumentSection::class, 'section_id');
    }

    /**
     * Get the field values for this custom field.
     */
    public function fieldValues()
    {
        return $this->hasMany(DocumentFieldValue::class, 'field_id');
    }

    /**
     * Get the field type label.
     */
    public function getTypeLabel()
    {
        return self::FIELD_TYPES[$this->type] ?? $this->type;
    }

    /**
     * Check if field has options (select, checkbox, radio).
     */
    public function hasOptions()
    {
        return in_array($this->type, ['select', 'checkbox', 'radio']);
    }

    /**
     * Get formatted options for form display.
     */
    public function getFormattedOptions()
    {
        if (!$this->hasOptions() || !$this->options) {
            return [];
        }

        $options = [];
        foreach ($this->options as $option) {
            if (is_array($option)) {
                $options[$option['value']] = $option['label'];
            } else {
                $options[$option] = $option;
            }
        }

        return $options;
    }

    /**
     * Scope for active fields.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered fields.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}