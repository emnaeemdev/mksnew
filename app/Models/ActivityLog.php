<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'route_name',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'properties',
        'status_code',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'subject_id' => 'integer',
            'status_code' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'login' => 'دخول',
            'login_failed' => 'دخول فاشل',
            'logout' => 'خروج',
            'created' => 'إضافة',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
            'published' => 'نشر',
            'unpublished' => 'إلغاء نشر',
            'exported' => 'تصدير',
            'downloaded' => 'تحميل',
            default => 'إجراء',
        };
    }

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'login' => 'bg-success',
            'login_failed' => 'bg-danger',
            'logout' => 'bg-secondary',
            'created' => 'bg-primary',
            'updated' => 'bg-warning text-dark',
            'deleted' => 'bg-danger',
            'published' => 'bg-success',
            'unpublished' => 'bg-dark',
            'exported', 'downloaded' => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }
};
