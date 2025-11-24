<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (! $request->user()) {
            return redirect('login');
        }

        // Check if user role is in the allowed roles
        if (! in_array($request->user()->role, $roles)) {
            // Redirect based on user role
            switch ($request->user()->role) {
                case 'user':
                    return redirect('/user/dashboard');
                case 'admin':
                    return redirect('/admin/dashboard');
                case 'superadmin':
                    return redirect('/superadmin/dashboard');
                default:
                    return redirect('/');
            }
        }

        return $next($request);
    }
}