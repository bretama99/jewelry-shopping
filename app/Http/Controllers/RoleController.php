<?php
// File: app/Http/Controllers/RoleController.php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:roles.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create')->only(['create', 'store']);
        $this->middleware('permission:roles.edit')->only(['edit', 'update']);
        $this->middleware('permission:roles.delete')->only(['destroy']);
        $this->middleware('permission:roles.manage')->only(['toggleStatus', 'bulkAction']);
    }

    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $query = Role::with('users');

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'sort_order');
        $sortOrder = $request->get('order', 'asc');

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'users_count':
                $query->withCount('users')->orderBy('users_count', $sortOrder);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            default:
                $query->ordered();
        }

        $roles = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total_roles' => Role::count(),
            'active_roles' => Role::where('is_active', true)->count(),
            'inactive_roles' => Role::where('is_active', false)->count(),
            'system_roles' => Role::whereIn('slug', ['super-admin', 'admin', 'customer'])->count(),
            'custom_roles' => Role::whereNotIn('slug', ['super-admin', 'admin', 'customer'])->count(),
            'users_with_roles' => \App\Models\User::whereNotNull('role_id')->count(),
        ];

        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'sort' => $sortBy,
            'order' => $sortOrder
        ];

        return view('roles.index', compact('roles', 'stats', 'filters'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $availablePermissions = Role::getAvailablePermissions();
        $permissionGroups = Role::getPermissionGroups();

        return view('roles.create', compact('availablePermissions', 'permissionGroups'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'slug' => 'nullable|string|max:255|unique:roles',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(Role::getAvailablePermissions())),
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            } else {
                $validated['slug'] = Str::slug($validated['slug']);
            }

            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['sort_order'] = $validated['sort_order'] ?? 0;
            $validated['permissions'] = $validated['permissions'] ?? [];

            $role = Role::create($validated);

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Role created successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load(['users' => function ($query) {
            $query->select('id', 'first_name', 'last_name', 'email', 'status', 'role_id')
                  ->orderBy('first_name');
        }]);

        $availablePermissions = Role::getAvailablePermissions();
        $permissionGroups = Role::getPermissionGroups();

        return view('roles.show', compact('role', 'availablePermissions', 'permissionGroups'));
    }

    /**
     * Show the form for editing the role
     */
    public function edit(Role $role)
    {
        $availablePermissions = Role::getAvailablePermissions();
        $permissionGroups = Role::getPermissionGroups();

        return view('roles.edit', compact('role', 'availablePermissions', 'permissionGroups'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(Role::getAvailablePermissions())),
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            } else {
                $validated['slug'] = Str::slug($validated['slug']);
            }

            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['sort_order'] = $validated['sort_order'] ?? $role->sort_order;
            $validated['permissions'] = $validated['permissions'] ?? [];

            $role->update($validated);

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Role updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        try {
            if (!$role->canBeDeleted()) {
                return redirect()->back()->with('error', 'Cannot delete this role. It has users assigned to it.');
            }

            if ($role->isDefault()) {
                return redirect()->back()->with('error', 'Cannot delete default system roles.');
            }

            $roleName = $role->name;
            $role->delete();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', "Role '{$roleName}' deleted successfully.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete role. Please try again.');
        }
    }

    /**
     * Toggle role status
     */
    public function toggleStatus(Role $role)
    {
        try {
            $role->update(['is_active' => !$role->is_active]);

            $status = $role->fresh()->is_active ? 'activated' : 'deactivated';
            return response()->json([
                'success' => true,
                'message' => "Role {$status} successfully.",
                'is_active' => $role->fresh()->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role status.'
            ], 500);
        }
    }

    /**
     * Duplicate role
     */
    public function duplicate(Role $role)
    {
        try {
            $newRole = $role->replicate();
            $newRole->name = $role->name . ' (Copy)';
            $newRole->slug = $role->slug . '-copy';
            $newRole->save();

            return redirect()
                ->route('roles.edit', $newRole)
                ->with('success', 'Role duplicated successfully. Please review and modify as needed.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to duplicate role: ' . $e->getMessage());
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id'
        ]);

        try {
            $roles = Role::whereIn('id', $request->role_ids);

            switch ($request->action) {
                case 'activate':
                    $roles->update(['is_active' => true]);
                    break;
                case 'deactivate':
                    $roles->update(['is_active' => false]);
                    break;
                case 'delete':
                    $roles->each(function ($role) {
                        if ($role->canBeDeleted() && !$role->isDefault()) {
                            $role->delete();
                        }
                    });
                    break;
            }

            $actionText = [
                'activate' => 'activated',
                'deactivate' => 'deactivated',
                'delete' => 'deleted'
            ];

            $count = count($request->role_ids);
            $message = "{$count} " . ($count === 1 ? 'role' : 'roles') . " {$actionText[$request->action]} successfully.";

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export roles
     */
    public function export(Request $request)
    {
        try {
            $roles = Role::when($request->status, function($query, $status) {
                    $query->where('is_active', $status === 'active');
                })
                ->with('users')
                ->get();

            $filename = 'roles_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function() use ($roles) {
                $handle = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($handle, [
                    'ID', 'Name', 'Slug', 'Description', 'Status', 'Users Count',
                    'Permissions Count', 'Sort Order', 'Created At', 'Updated At'
                ]);

                // Add data rows
                foreach ($roles as $role) {
                    fputcsv($handle, [
                        $role->id,
                        $role->name,
                        $role->slug,
                        $role->description,
                        $role->is_active ? 'Active' : 'Inactive',
                        $role->users->count(),
                        count($role->permissions ?? []),
                        $role->sort_order,
                        $role->created_at->format('Y-m-d H:i:s'),
                        $role->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($handle);
            }, 200, $headers);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export roles: ' . $e->getMessage());
        }
    }
}
