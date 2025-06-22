<?php
// File: app/Models/Role.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (!$role->slug) {
                $role->slug = Str::slug($role->name);
            }
        });

        static::updating(function ($role) {
            if ($role->isDirty('name') && !$role->isDirty('slug')) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    // Accessors
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'bg-success' : 'bg-secondary';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    // Permission methods
    public function hasPermission($permission)
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    public function givePermission($permission)
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
        return $this;
    }

    public function revokePermission($permission)
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, function($p) use ($permission) {
            return $p !== $permission;
        });
        $this->permissions = array_values($permissions);
        $this->save();
        return $this;
    }

    public function syncPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        $this->save();
        return $this;
    }

    // Helper methods
    public function canBeDeleted()
    {
        return $this->users()->count() === 0;
    }

    public function isDefault()
    {
        return in_array($this->slug, ['super-admin', 'admin', 'customer']);
    }

    // Static methods
    public static function getAvailablePermissions()
    {
        return [
            // User Management
            'users.view' => 'View Users',
            'users.create' => 'Create Users',
            'users.edit' => 'Edit Users',
            'users.delete' => 'Delete Users',
            'users.manage' => 'Manage Users',

            // Role Management
            'roles.view' => 'View Roles',
            'roles.create' => 'Create Roles',
            'roles.edit' => 'Edit Roles',
            'roles.delete' => 'Delete Roles',
            'roles.manage' => 'Manage Roles',

            // Category Management
            'categories.view' => 'View Categories',
            'categories.create' => 'Create Categories',
            'categories.edit' => 'Edit Categories',
            'categories.delete' => 'Delete Categories',
            'categories.manage' => 'Manage Categories',

            // Product Management
            'products.view' => 'View Products',
            'products.create' => 'Create Products',
            'products.edit' => 'Edit Products',
            'products.delete' => 'Delete Products',
            'products.manage' => 'Manage Products',

            // Order Management
            'orders.view' => 'View Orders',
            'orders.create' => 'Create Orders',
            'orders.edit' => 'Edit Orders',
            'orders.delete' => 'Delete Orders',
            'orders.manage' => 'Manage Orders',

            // Admin Panel
            'admin.access' => 'Access Admin Panel',
            'admin.dashboard' => 'View Admin Dashboard',
            'admin.settings' => 'Manage Settings',
            'admin.reports' => 'View Reports',

        ];
    }

    public static function getPermissionGroups()
    {
        return [
            'User Management' => ['users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage'],
            'Role Management' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.manage'],
            'Category Management' => ['categories.view', 'categories.create', 'categories.edit', 'categories.delete', 'categories.manage'],
            'Product Management' => ['products.view', 'products.create', 'products.edit', 'products.delete', 'products.manage'],
            'Order Management' => ['orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.manage'],
            'Admin Panel' => ['admin.access', 'admin.dashboard', 'admin.settings', 'admin.reports'],
        ];
    }
}
