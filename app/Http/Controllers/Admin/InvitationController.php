<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminInvitation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InvitationController extends Controller
{
    /**
     * Show the password creation form for invited administrator.
     */
    public function showAcceptForm(string $token): View
    {
        $invitation = AdminInvitation::where('token', $token)->first();

        if (!$invitation || !$invitation->isValid()) {
            $isExpired = $invitation && $invitation->isExpired();
            $isAccepted = $invitation && $invitation->isAccepted();

            return view('admin.invitations.invalid', compact('isExpired', 'isAccepted'));
        }

        return view('admin.invitations.accept', compact('invitation', 'token'));
    }

    /**
     * Process password setup and account activation.
     */
    public function processAccept(string $token, Request $request): RedirectResponse
    {
        $invitation = AdminInvitation::where('token', $token)->first();

        if (!$invitation || !$invitation->isValid()) {
            return redirect()->route('admin.login')
                ->with('error', 'This invitation link is invalid or has expired.');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = $invitation->admin ?? Admin::where('email', $invitation->email)->first();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Associated administrator account was not found.');
        }

        // Set password and activate account
        $admin->password = Hash::make($request->input('password'));
        $admin->status = Admin::STATUS_ACTIVE;
        $admin->save();

        // Mark invitation as accepted
        $invitation->accepted_at = Carbon::now();
        $invitation->save();

        // Log the newly activated administrator in
        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('success', "Welcome, {$admin->name}! Your account has been activated successfully.");
    }
}
