<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Redirect to the customer profile (customers use profile/orders instead of a dashboard).
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('profile');
    }
}
