<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements MustVerifyEmail, Auditable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get the recovery codes as an array.
     */
    public function getRecoveryCodes(): array
    {
        $codes = json_decode($this->two_factor_recovery_codes ?? '[]', true);
        return is_array($codes) ? $codes : [];
    }

    // ─── Audit Configuration ────────────────────────────────────────

    /**
     * Audit configurations for the User model.
     */
    public function getAuditInclude(): array
    {
        return [
            'name',
            'email',
            'role',
            'email_verified_at',
            'two_factor_confirmed_at',
        ];
    }

    /**
     * Attributes to exclude from audit.
     */
    public function getAuditExclude(): array
    {
        return [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];
    }

    /**
     * Redact sensitive data in audit logs.
     */
    public function transformAudit(array $data): array
    {
        if (isset($data['old']['email'])) {
            $data['old']['email'] = $this->redactEmail($data['old']['email']);
        }
        if (isset($data['new']['email'])) {
            $data['new']['email'] = $this->redactEmail($data['new']['email']);
        }
        
        return $data;
    }

    protected function redactEmail($email)
    {
        if (!$email) return '';
        
        $parts = explode('@', $email);
        $username = $parts[0];
        $domain = $parts[1] ?? '';
        
        if (strlen($username) <= 4) {
            $redactedUsername = str_repeat('*', strlen($username));
        } else {
            $redactedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 4) . substr($username, -2);
        }
        
        return $redactedUsername . '@' . $domain;
    }
}
