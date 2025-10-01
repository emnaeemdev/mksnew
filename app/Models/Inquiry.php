<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'read_at',
        'locale',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_READ = 'read';

    public function markRead(): void
    {
        $this->status = self::STATUS_READ;
        $this->read_at = now();
        $this->save();
    }

    public function markUnread(): void
    {
        $this->status = self::STATUS_NEW;
        $this->read_at = null;
        $this->save();
    }
}