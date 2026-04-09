<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'telephone',
        'role',
        'image',
        'status',
        'password',
        'password_updates'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // In your User model
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isRegularUser()
    {
        return $this->role === 'user';
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot(['can_view', 'can_create', 'can_update', 'can_delete', 'can_toggle_status'])
            ->withTimestamps();
    }

    public function hasAnyAccess(): bool
    {
        return $this->permissions()
            ->where(function ($query) {
                $query
                    ->where('user_permissions.can_view', true)
                    ->orWhere('user_permissions.can_create', true)
                    ->orWhere('user_permissions.can_update', true)
                    ->orWhere('user_permissions.can_delete', true)
                    ->orWhere('user_permissions.can_toggle_status', true);
            })
            ->exists();
    }

    public function canAccess(string $key, string $ability = 'view'): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $column = match ($ability) {
            'view' => 'can_view',
            'create' => 'can_create',
            'update' => 'can_update',
            'delete' => 'can_delete',
            'toggle_status' => 'can_toggle_status',
            default => null,
        };

        if (!$column) {
            return false;
        }

        return $this->permissions()
            ->where('key', $key)
            ->wherePivot($column, true)
            ->exists();
    }
}
