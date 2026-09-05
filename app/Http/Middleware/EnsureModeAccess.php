<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureModeAccess
{
    /**
     * Handle an incoming request ensuring admin has access to the requested shopping mode.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in with an administrator account.');
        }

        // Super Admin has global access to all modes
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check explicit route binding for Product
        $product = $request->route('product');
        if ($product instanceof Product) {
            if (!$admin->hasModeAccess($product->mode_id)) {
                abort(403, 'Unauthorized. You do not have permission to access products in this shopping mode.');
            }
        }

        // Check explicit route binding for Category
        $category = $request->route('category');
        if ($category instanceof Category && $category->mode_id) {
            if (!$admin->hasModeAccess($category->mode_id)) {
                abort(403, 'Unauthorized. You do not have permission to access categories in this shopping mode.');
            }
        }

        // Check explicit route binding for Mode
        $mode = $request->route('mode');
        if ($mode instanceof Mode) {
            if (!$admin->hasModeAccess($mode->id)) {
                abort(403, 'Unauthorized. You do not have permission to manage this shopping mode.');
            }
        } elseif (is_numeric($mode) || is_string($mode)) {
            if (!$admin->hasModeAccess($mode)) {
                abort(403, 'Unauthorized. You do not have permission to manage this shopping mode.');
            }
        }

        // Check request payload input for mode_id on store/update operations
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            if ($request->has('mode_id') && !empty($request->input('mode_id'))) {
                $requestedModeId = $request->input('mode_id');
                if (!$admin->hasModeAccess($requestedModeId)) {
                    abort(403, 'Unauthorized. You cannot assign items to a shopping mode you do not have permission to manage.');
                }
            }
        }

        return $next($request);
    }
}
