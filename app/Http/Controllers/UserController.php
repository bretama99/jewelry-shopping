<?php
// File: app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.edit')->only(['edit', 'update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
        $this->middleware('permission:users.manage')->only(['toggleStatus', 'toggleAdmin', 'bulkAction']);
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['role']);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_type')) {
            if ($request->user_type === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->user_type === 'customer') {
                $query->where('is_admin', false);
            }
        }

        if ($request->filled('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('slug', $request->role);
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        switch ($sortBy) {
            case 'name':
                $query->orderBy('first_name', $sortOrder)->orderBy('last_name', $sortOrder);
                break;
            case 'email':
                $query->orderBy('email', $sortOrder);
                break;
            case 'status':
                $query->orderBy('status', $sortOrder);
                break;
            case 'role':
                $query->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                      ->orderBy('roles.name', $sortOrder)
                      ->select('users.*');
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $users = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'inactive_users' => User::inactive()->count(),
            'suspended_users' => User::suspended()->count(),
            'admin_users' => User::admins()->count(),
            'customer_users' => User::customers()->count(),
        ];

        // Get roles for filter
        $roles = Role::active()->ordered()->get();

        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'user_type' => $request->get('user_type'),
            'role' => $request->get('role'),
            'sort' => $sortBy,
            'order' => $sortOrder
        ];

        return view('auth.index', compact('users', 'stats', 'filters', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::active()->ordered()->get();
        return view('auth.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'passport_id_number' => 'nullable|string|max:50',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive,suspended',
            'is_admin' => 'boolean',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        try {
            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $image = $request->file('profile_picture');
                $fileName = time() . '_' . str_replace(' ', '_', strtolower($validated['first_name'] . '_' . $validated['last_name'])) . '.' . $image->getClientOriginalExtension();

                // Create directory if it doesn't exist
                $uploadPath = public_path('images/users');
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0777, true);
                }

                // Move the uploaded file
                $image->move($uploadPath, $fileName);
                $validated['profile_picture'] = $fileName;
            }

            $validated['password'] = Hash::make($validated['password']);
            $validated['is_admin'] = $request->boolean('is_admin');

            // If no role specified, assign default customer role
            if (!$validated['role_id']) {
                $defaultRole = Role::where('slug', 'customer')->first();
                if ($defaultRole) {
                    $validated['role_id'] = $defaultRole->id;
                }
            }

            $user = User::create($validated);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['role', 'orders']);

        return view('auth.show', compact('user'));
    }

    /**
     * Show the form for editing the user
     */
    public function edit(User $user)
    {
        $roles = Role::active()->ordered()->get();
        return view('auth.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'passport_id_number' => 'nullable|string|max:50',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive,suspended',
            'is_admin' => 'boolean',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        try {
            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old image
                if ($user->profile_picture && File::exists(public_path('images/users/' . $user->profile_picture))) {
                    File::delete(public_path('images/users/' . $user->profile_picture));
                }

                // Store new image
                $image = $request->file('profile_picture');
                $fileName = time() . '_' . str_replace(' ', '_', strtolower($validated['first_name'] . '_' . $validated['last_name'])) . '.' . $image->getClientOriginalExtension();

                // Create directory if it doesn't exist
                $uploadPath = public_path('images/users');
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0777, true);
                }

                // Move the uploaded file
                $image->move($uploadPath, $fileName);
                $validated['profile_picture'] = $fileName;
            }

            // Handle image removal
            if ($request->has('remove_profile_picture') && $request->remove_profile_picture) {
                if ($user->profile_picture && File::exists(public_path('images/users/' . $user->profile_picture))) {
                    File::delete(public_path('images/users/' . $user->profile_picture));
                }
                $validated['profile_picture'] = null;
            }

            // Only update password if provided
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $validated['is_admin'] = $request->boolean('is_admin');

            $user->update($validated);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        try {
            if (!$user->canBeDeleted()) {
                return redirect()->back()->with('error', 'Cannot delete this user. User may have associated data or is a super admin.');
            }

            $userName = $user->name;

            // Delete profile picture
            if ($user->profile_picture && File::exists(public_path('images/users/' . $user->profile_picture))) {
                File::delete(public_path('images/users/' . $user->profile_picture));
            }

            $user->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('success', "User '{$userName}' deleted successfully.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete user. Please try again.');
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        try {
            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            $user->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => "User status updated to {$newStatus}.",
                'status' => $newStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status.'
            ], 500);
        }
    }

    /**
     * Toggle admin status
     */
    public function toggleAdmin(User $user)
    {
        try {
            $user->update(['is_admin' => !$user->is_admin]);

            $status = $user->fresh()->is_admin ? 'promoted to admin' : 'removed from admin';
            return response()->json([
                'success' => true,
                'message' => "User {$status} successfully.",
                'is_admin' => $user->fresh()->is_admin
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin status.'
            ], 500);
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        try {
            $role = Role::find($request->role_id);
            $user->update(['role_id' => $role->id]);

            return response()->json([
                'success' => true,
                'message' => "User assigned to role '{$role->name}' successfully.",
                'role' => $role
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role.'
            ], 500);
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,suspend,delete,make_admin,remove_admin,assign_role',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required_if:action,assign_role|exists:roles,id'
        ]);

        try {
            $users = User::whereIn('id', $request->user_ids);

            switch ($request->action) {
                case 'activate':
                    $users->update(['status' => 'active']);
                    break;
                case 'deactivate':
                    $users->update(['status' => 'inactive']);
                    break;
                case 'suspend':
                    $users->update(['status' => 'suspended']);
                    break;
                case 'make_admin':
                    $users->update(['is_admin' => true]);
                    break;
                case 'remove_admin':
                    $users->update(['is_admin' => false]);
                    break;
                case 'assign_role':
                    $users->update(['role_id' => $request->role_id]);
                    break;
                case 'delete':
                    $users->each(function ($user) {
                        if ($user->canBeDeleted()) {
                            if ($user->profile_picture && File::exists(public_path('images/users/' . $user->profile_picture))) {
                                File::delete(public_path('images/users/' . $user->profile_picture));
                            }
                            $user->delete();
                        }
                    });
                    break;
            }

            $actionText = [
                'activate' => 'activated',
                'deactivate' => 'deactivated',
                'suspend' => 'suspended',
                'make_admin' => 'promoted to admin',
                'remove_admin' => 'removed from admin',
                'assign_role' => 'assigned to role',
                'delete' => 'deleted'
            ];

            $count = count($request->user_ids);
            $message = "{$count} " . ($count === 1 ? 'user' : 'users') . " {$actionText[$request->action]} successfully.";

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
     * Export users
     */
    public function export(Request $request)
    {
        try {
            $users = User::with('role')
                ->when($request->status, function($query, $status) {
                    $query->where('status', $status);
                })
                ->when($request->user_type, function($query, $type) {
                    if ($type === 'admin') {
                        $query->where('is_admin', true);
                    } elseif ($type === 'customer') {
                        $query->where('is_admin', false);
                    }
                })
                ->when($request->role, function($query, $role) {
                    $query->whereHas('role', function($q) use ($role) {
                        $q->where('slug', $role);
                    });
                })
                ->get();

            $filename = 'users_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function() use ($users) {
                $handle = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($handle, [
                    'ID', 'First Name', 'Last Name', 'Email', 'Phone',
                    'Passport/ID Number', 'Status', 'Admin', 'Role', 'Created At', 'Updated At'
                ]);

                // Add data rows
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id,
                        $user->first_name,
                        $user->last_name,
                        $user->email,
                        $user->phone,
                        $user->passport_id_number,
                        ucfirst($user->status),
                        $user->is_admin ? 'Yes' : 'No',
                        $user->role ? $user->role->name : 'No Role',
                        $user->created_at->format('Y-m-d H:i:s'),
                        $user->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($handle);
            }, 200, $headers);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export users: ' . $e->getMessage());
        }
    }
}
