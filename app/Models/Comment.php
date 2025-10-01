<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'content',
        'post_id',
        'status',
        'ip_address'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the post that owns the comment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Scope a query to only include approved comments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending comments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the status text in Arabic.
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'approved' => 'موافق عليه',
            'pending' => 'في الانتظار',
            'rejected' => 'مرفوض',
            default => 'غير محدد'
        };
    }
}