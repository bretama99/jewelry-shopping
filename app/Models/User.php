<?php
// File: app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'profile_picture',
        'passport_id_number',
        'status',
        'is_admin',
        'role_id',
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
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Auto-assign default role if none specified
            if (!$user->role_id) {
                $defaultRole = \App\Models\Role::where('slug', 'customer')->first();
                if ($defaultRole) {
                    $user->role_id = $defaultRole->id;
                }
            }
        });

        static::deleting(function ($user) {
            if ($user->profile_picture && File::exists(public_path('images/users/' . $user->profile_picture))) {
                File::delete(public_path('images/users/' . $user->profile_picture));
            }
        });
    }

    // Relationships
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function cartItems()
    {
        return $this->hasMany(\App\Models\CartItem::class);
    }

    // Accessors
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture && File::exists(public_path('images/users/' . $this->profile_picture))) {
            return asset('images/users/' . $this->profile_picture);
        }
        return asset('images/default-avatar.png');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'suspended' => 'bg-danger'
        ];
        return $badges[$this->status] ?? 'bg-secondary';
    }

    public function getStatusTextAttribute()
    {
        return ucfirst($this->status);
    }

    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    public function getRoleNameAttribute()
    {
        return $this->role ? $this->role->name : 'No Role';
    }

    public function getRoleBadgeAttribute()
    {
        if (!$this->role) {
            return 'bg-secondary';
        }

        switch ($this->role->slug) {
            case 'super-admin':
                return 'bg-danger';
            case 'admin':
                return 'bg-warning';
            case 'manager':
                return 'bg-info';
            case 'customer':
                return 'bg-primary';
            default:
                return 'bg-secondary';
        }
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeCustomers($query)
    {
        return $query->where('is_admin', false);
    }

    public function scopeWithRole($query, $roleSlug)
    {
        return $query->whereHas('role', function ($q) use ($roleSlug) {
            $q->where('slug', $roleSlug);
        });
    }

    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            return $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('passport_id_number', 'like', "%{$search}%")
                  ->orWhereHas('role', function ($roleQuery) use ($search) {
                      $roleQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        return $query;
    }

    // Permission methods
    public function hasPermission($permission)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($permission);
    }

    public function hasAnyPermission(array $permissions)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissions)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function can($ability, $arguments = [])
    {
        // Check Laravel's built-in authorization first
        $result = parent::can($ability, $arguments);

        if ($result !== false) {
            return $result;
        }

        // Check our custom permission system
        return $this->hasPermission($ability);
    }

    // Role methods
    public function hasRole($roleSlug)
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    public function assignRole($roleSlug)
    {
        $role = \App\Models\Role::where('slug', $roleSlug)->first();
        if ($role) {
            $this->role_id = $role->id;
            $this->save();
        }
        return $this;
    }

    public function removeRole()
    {
        $this->role_id = null;
        $this->save();
        return $this;
    }

    // Helper methods
    public function canBeDeleted()
    {
        // Add logic to check if user can be deleted
        // For example, check if user has orders, etc.
        return !$this->isSuperAdmin() && $this->orders()->count() === 0;
    }

    public function isAdmin()
    {
        return $this->is_admin || $this->hasRole('admin') || $this->hasRole('super-admin');
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('super-admin');
    }

    // ADDED: Missing isCustomer method that your middleware was looking for
    public function isCustomer()
    {
        return !$this->is_admin && ($this->hasRole('customer') || !$this->role);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function canAccessAdmin()
    {
        return $this->isActive() && ($this->isAdmin() || $this->hasPermission('admin.access'));
    }

    public function getPermissions()
    {
        if ($this->isSuperAdmin()) {
            // Return all available permissions for super admin
            return [
                'manage_users',
                'manage_roles',
                'manage_products',
                'manage_orders',
                'manage_categories',
                'manage_metal_categories',
                'manage_settings',
                'view_reports',
                'system_maintenance'
            ];
        }

        return $this->role ? $this->role->permissions ?? [] : [];
    }

    
}