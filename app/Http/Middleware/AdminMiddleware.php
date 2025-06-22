<?php
// File: app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has admin permissions using multiple methods
        $isAdmin = false;

        // Method 1: Check is_admin flag
        if (isset($user->is_admin) && $user->is_admin) {
            $isAdmin = true;
        }

        // Method 2: Check role relationship
        if (!$isAdmin && $user->role) {
            $adminRoles = ['admin', 'super-admin', 'manager'];
            if (in_array($user->role->slug, $adminRoles)) {
                $isAdmin = true;
            }
        }

        // Method 3: Check using methods if they exist
        if (!$isAdmin) {
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                $isAdmin = true;
            }
            
            if (!$isAdmin && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                $isAdmin = true;
            }
            
            if (!$isAdmin && method_exists($user, 'hasRole')) {
                if ($user->hasRole('admin') || $user->hasRole('super-admin') || $user->hasRole('manager')) {
                    $isAdmin = true;
                }
            }
        }

        if (!$isAdmin) {
            // Redirect based on user's actual role
            if ($user->isCustomer() || ($user->role && $user->role->slug === 'customer')) {
                return redirect()->route('customer.dashboard')
                    ->with('error', 'You do not have admin access.');
            }
            
            // For users without proper roles, redirect to home
            return redirect()->route('external.home')
                ->with('error', 'You do not have admin access.');
        }

        return $next($request);
    }
}