<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSearchToken extends Model
{
    protected $fillable = [
        'document_id',
        'token',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
