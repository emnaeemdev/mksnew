<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'is_published',
        'is_featured',
        'published_at',
        'views_count',
        'user_id'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'views_count' => 'integer'
    ];

    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->slug)) {
                $document->slug = Str::slug($document->title);
            }
            if (empty($document->published_at) && $document->is_published) {
                $document->published_at = now();
            }
        });

        static::updating(function ($document) {
            if ($document->isDirty('title') && empty($document->slug)) {
                $document->slug = Str::slug($document->title);
            }
            if ($document->isDirty('is_published') && $document->is_published && empty($document->published_at)) {
                $document->published_at = now();
            }
        });
    }

    /**
     * Get the document section that owns this document.
     */
    public function section()
    {
        return $this->belongsTo(DocumentSection::class, 'section_id');
    }

    /**
     * Get the user that created this document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the field values for this document.
     */
    public function fieldValues()
    {
        return $this->hasMany(DocumentFieldValue::class, 'document_id')
            ->join('document_custom_fields', 'document_field_values.field_id', '=', 'document_custom_fields.id')
            ->orderBy('document_custom_fields.sort_order')
            ->select('document_field_values.*');
    }

    /**
     * Get field value by field name.
     */
    public function getFieldValue($fieldName)
    {
        $fieldValue = $this->fieldValues()
            ->whereHas('field', function ($query) use ($fieldName) {
                $query->where('name', $fieldName);
            })
            ->first();

        return $fieldValue ? $fieldValue->value : null;
    }

    /**
     * Get all field values as key-value pairs.
     */
    public function getFieldValuesArray()
    {
        $values = [];
        foreach ($this->fieldValues as $fieldValue) {
            $values[$fieldValue->field->name] = $fieldValue->value;
        }
        return $values;
    }

    /**
     * Increment views count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        // إذا كان الطلب من لوحة التحكم، استخدم ID
        if (request()->is('admin/*')) {
            return 'id';
        }
        // للواجهة الأمامية، استخدم slug
        return 'slug';
    }

    /**
     * Scope for published documents.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for featured documents.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for recent documents.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Scope for popular documents.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('views_count', 'desc');
    }

    /**
     * Search documents by title and content.
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhere('excerpt', 'like', "%{$search}%");
        });
    }

    /**
     * Get the files for the document.
     */
    public function files()
    {
        return $this->hasMany(DocumentFile::class)->orderBy('sort_order');
    }

    /**
     * Check if document has files.
     */
    public function hasFiles()
    {
        return $this->files()->exists();
    }
}