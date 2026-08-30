<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        $guard = $guard ?? ($request->is('admin*') ? 'admin' : 'web');
        $user = Auth::guard($guard)->user();

        if ($user && !$user->isActive()) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $route = $guard === 'admin' ? 'admin.login' : 'login';
            return redirect()->route($route)->with('error', "Your account is {$user->status}. Please contact support.");
        }

        return $next($request);
    }
}
