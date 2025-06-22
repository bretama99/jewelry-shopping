<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
 /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If no roles specified, just check if authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        $hasRole = false;
        
        foreach ($roles as $role) {
            // Handle different role checking methods
            if ($role === 'admin' && ($user->isAdmin() || $user->is_admin)) {
                $hasRole = true;
                break;
            }
            
            if ($role === 'super-admin' && ($user->isSuperAdmin() || $user->hasRole('super-admin'))) {
                $hasRole = true;
                break;
            }
            
            if ($role === 'customer' && ($user->isCustomer() || $user->hasRole('customer'))) {
                $hasRole = true;
                break;
            }
            
            // Check using the role relationship
            if ($user->role && $user->role->slug === $role) {
                $hasRole = true;
                break;
            }
            
            // Check using hasRole method if it exists
            if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            // Redirect based on user's actual role
            if ($user->isCustomer() || ($user->role && $user->role->slug === 'customer')) {
                return redirect()->route('customer.dashboard')
                    ->with('error', 'You do not have permission to access this area.');
            }
            
            // For users without proper roles, redirect to home
            return redirect()->route('external.home')
                ->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
