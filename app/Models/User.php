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
    use \Laravel\Sanctum\HasApiTokens;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'store_name',
        'store_description',
        'seller_status',
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

    /**
     * Verified purchase: the user has received this book (it is in one of their
     * orders that was shipped or completed). Only then may they review it.
     */
    public function hasPurchased(Book $book): bool
    {
        return $this->orders()
            ->whereIn('status', ['shipped', 'completed'])
            ->whereHas('orderItems', fn ($q) => $q->where('book_id', $book->id))
            ->exists();
    }

    /** Book listings owned by this seller. */
    public function books()
    {
        return $this->hasMany(Book::class, 'seller_id');
    }

    /** Orders this seller has to fulfil. */
    public function sellerOrders()
    {
        return $this->hasMany(Order::class, 'seller_id');
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

    /** Buyers are stored with the "customer" role. */
    public function isBuyer(): bool
    {
        return $this->isCustomer();
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    /** A seller an admin has approved; only these can list books and receive orders. */
    public function isApprovedSeller(): bool
    {
        return $this->isSeller() && $this->seller_status === 'approved';
    }

    /** Name shown to buyers for this seller's store. */
    public function getStoreDisplayNameAttribute(): string
    {
        return $this->store_name ?: $this->name;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'seller' => 'Seller',
            default => 'Buyer',
        };
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
            'seller_status',
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
