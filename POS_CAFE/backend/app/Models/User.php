<?php

namespace App\Models;

use App\Enums\RoleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(CashierShift::class);
    }

    public function activeShift(): ?CashierShift
    {
        return $this->shifts()->where('status', 'open')->latest()->first();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasRole(string|array|RoleType $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        if ($roles instanceof RoleType) {
            return $this->role->slug === $roles->value;
        }

        if (is_array($roles)) {
            $slugs = array_map(fn($r) => $r instanceof RoleType ? $r->value : $r, $roles);
            return in_array($this->role->slug, $slugs, true);
        }

        return $this->role->slug === $roles;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleType::ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(RoleType::MANAGER);
    }

    public function isCashier(): bool
    {
        return $this->hasRole(RoleType::CASHIER);
    }

    public function isBarista(): bool
    {
        return $this->hasRole(RoleType::BARISTA);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->role && $this->role->permissions()->where('slug', $permissionSlug)->exists();
    }
}
