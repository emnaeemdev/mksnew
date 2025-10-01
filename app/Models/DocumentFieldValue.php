<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'field_id',
        'value'
    ];

    protected $casts = [
        'value' => 'string'
    ];

    /**
     * Get the document that owns this field value.
     */
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * Get the custom field that owns this field value.
     */
    public function field()
    {
        return $this->belongsTo(DocumentCustomField::class, 'field_id');
    }

    /**
     * Get formatted value based on field type.
     */
    public function getFormattedValue()
    {
        if (!$this->field) {
            return $this->value;
        }

        switch ($this->field->type) {
            case 'date':
                if ($this->value) {
                    return \Carbon\Carbon::parse($this->value)->format('Y-m-d');
                }
                break;

            case 'datetime':
                if ($this->value) {
                    return \Carbon\Carbon::parse($this->value)->format('Y-m-d H:i');
                }
                break;

            case 'checkbox':
                if ($this->value) {
                    $values = is_array($this->value) ? $this->value : json_decode($this->value, true);
                    return is_array($values) ? implode(', ', $values) : $this->value;
                }
                break;

            case 'select':
            case 'radio':
                if ($this->field->hasOptions() && $this->value) {
                    $options = $this->field->getFormattedOptions();
                    return $options[$this->value] ?? $this->value;
                }
                break;

            case 'file':
            case 'image':
                if ($this->value) {
                    return basename($this->value);
                }
                break;

            case 'url':
                if ($this->value) {
                    return '<a href="' . $this->value . '" target="_blank">' . $this->value . '</a>';
                }
                break;

            case 'email':
                if ($this->value) {
                    return '<a href="mailto:' . $this->value . '">' . $this->value . '</a>';
                }
                break;
        }

        return $this->value;
    }

    /**
     * Get display value for frontend.
     */
    public function getDisplayValue()
    {
        if (!$this->field) {
            return $this->value;
        }

        switch ($this->field->type) {
            case 'date':
                if ($this->value) {
                    return \Carbon\Carbon::parse($this->value)->format('d/m/Y');
                }
                break;

            case 'datetime':
                if ($this->value) {
                    return \Carbon\Carbon::parse($this->value)->format('d/m/Y H:i');
                }
                break;

            case 'checkbox':
                if ($this->value) {
                    $values = is_array($this->value) ? $this->value : json_decode($this->value, true);
                    return is_array($values) ? implode('، ', $values) : $this->value;
                }
                break;

            case 'select':
            case 'radio':
                if ($this->field->hasOptions() && $this->value) {
                    $options = $this->field->getFormattedOptions();
                    return $options[$this->value] ?? $this->value;
                }
                break;

            case 'file':
            case 'image':
                if ($this->value) {
                    return basename($this->value);
                }
                break;
        }

        return $this->value;
    }

    /**
     * Check if value is empty.
     */
    public function isEmpty()
    {
        return empty($this->value) || $this->value === '' || $this->value === null;
    }
}