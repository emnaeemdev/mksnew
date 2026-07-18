<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';

    public const MAX_LOGIN_ATTEMPTS = 5;
    public const LOCKOUT_MINUTES = 15;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'failed_login_attempts',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locked_until' => 'datetime',
            'failed_login_attempts' => 'integer',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? self::ROLE_ADMIN) === self::ROLE_ADMIN;
    }

    public function isEditor(): bool
    {
        return ($this->role ?? self::ROLE_EDITOR) === self::ROLE_EDITOR;
    }

    public function isStaff(): bool
    {
        return in_array($this->role ?? self::ROLE_EDITOR, [self::ROLE_ADMIN, self::ROLE_EDITOR], true);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function registerFailedLogin(): void
    {
        $attempts = (int) $this->failed_login_attempts + 1;
        $data = ['failed_login_attempts' => $attempts];

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $data['locked_until'] = now()->addMinutes(self::LOCKOUT_MINUTES);
            $data['failed_login_attempts'] = 0;
        }

        $this->forceFill($data)->save();
    }

    public function clearLoginThrottle(): void
    {
        if ((int) $this->failed_login_attempts === 0 && $this->locked_until === null) {
            return;
        }

        $this->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }

    public static function roleLabels(): array
    {
        return [
            self::ROLE_ADMIN => 'مدير النظام',
            self::ROLE_EDITOR => 'محرر محتوى',
        ];
    }
}
