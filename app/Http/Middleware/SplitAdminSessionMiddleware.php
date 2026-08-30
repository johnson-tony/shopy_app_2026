<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Give the admin panel and the storefront their own independent session
 * cookies. Because each URL space binds to its own cookie, an admin logged
 * into the panel (e.g. in one tab) and a customer logged into the storefront
 * (e.g. in another tab) never share a session - logging out of one does not
 * affect the other.
 */
class SplitAdminSessionMiddleware
{
    /**
     * Cookie names used for each area.
     */
    public const ADMIN_COOKIE = 'shopy_admin_session';
    public const STORE_COOKIE = 'shopy_session';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAdmin = $request->is('admin') || $request->is('admin/*');

        config(['session.cookie' => $isAdmin ? self::ADMIN_COOKIE : self::STORE_COOKIE]);

        return $next($request);
    }
}
