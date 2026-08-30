<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->is('admin*')) {
                return redirect()->route('admin.login')->with('error', 'Please log in with an administrator account.');
            }
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        if (!$user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your account is not active. Please contact support.');
        }

        if (!$user->hasPermission($permission)) {
            abort(403, "Unauthorized. You do not have the '{$permission}' permission.");
        }

        return $next($request);
    }
}
