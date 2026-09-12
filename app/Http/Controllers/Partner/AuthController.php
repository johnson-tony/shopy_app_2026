<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\PartnerLoginRequest;
use App\Http\Requests\Partner\PartnerRegisterRequest;
use App\Models\DeliveryPartner;
use App\Models\Mode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the partner login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('partner')->check()) {
            return redirect()->route('partner.dashboard');
        }

        return view('partner.pages.auth.login');
    }

    /**
     * Display the partner registration & KYC onboarding view.
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::guard('partner')->check()) {
            return redirect()->route('partner.dashboard');
        }

        $modes = Mode::where('status', true)->orderBy('sort_order')->get();

        return view('partner.pages.auth.register', compact('modes'));
    }

    /**
     * Handle delivery partner onboarding registration with KYC document uploads.
     */
    public function register(PartnerRegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $licenseImagePath = null;
        if ($request->hasFile('license_image')) {
            try {
                $licenseImagePath = $request->file('license_image')->store('kyc/licenses', 'public');
            } catch (\Throwable $e) {
                // Upload fallback
            }
        }

        $idProofImagePath = null;
        if ($request->hasFile('id_proof_image')) {
            try {
                $idProofImagePath = $request->file('id_proof_image')->store('kyc/id_proofs', 'public');
            } catch (\Throwable $e) {
                // Upload fallback
            }
        }

        $partner = DeliveryPartner::create([
            'name'                => $validated['name'],
            'email'               => $validated['email'],
            'phone'               => $validated['phone'],
            'password'            => Hash::make($validated['password']),
            'vehicle_type'        => $validated['vehicle_type'],
            'vehicle_number'      => $validated['vehicle_number'] ?? null,
            'license_number'      => $validated['license_number'] ?? null,
            'license_image'       => $licenseImagePath,
            'id_proof_type'       => $validated['id_proof_type'] ?? null,
            'id_proof_number'     => $validated['id_proof_number'] ?? null,
            'id_proof_image'      => $idProofImagePath,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_ifsc'           => $validated['bank_ifsc'] ?? null,
            'upi_id'              => $validated['upi_id'] ?? null,
            'status'              => DeliveryPartner::STATUS_PENDING_APPROVAL,
            'is_available'        => false,
            'location_source'     => DeliveryPartner::LOCATION_SOURCE_STATIC,
        ]);

        // Attach chosen shopping modes (Shopy, Minutes, Food)
        if (!empty($validated['modes'])) {
            $partner->modes()->sync($validated['modes']);
        }

        return redirect()->route('partner.login')->with('success', 'Application submitted successfully! Your KYC documents are under review by our dispatch operations team. You will be activated once verified.');
    }

    /**
     * Handle a partner login request.
     */
    public function login(PartnerLoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::guard('partner')->attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our delivery partner records.']);
        }

        $partner = Auth::guard('partner')->user();

        // Verify active status
        if (!$partner->isActive()) {
            Auth::guard('partner')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($partner->isPendingApproval()) {
                return redirect()->route('partner.login')
                    ->with('warning', 'Your delivery partner account and KYC documents are currently under review. Our dispatch team will approve your profile shortly.');
            }

            if ($partner->isRejected()) {
                $reason = $partner->rejection_reason ? " Reason: {$partner->rejection_reason}" : '';
                return redirect()->route('partner.login')
                    ->with('error', "Your delivery partner KYC was declined.{$reason} Please contact operations support.");
            }

            return redirect()->route('partner.login')
                ->with('error', "Your delivery partner account is {$partner->status}. Please contact dispatch support.");
        }

        $request->session()->regenerate();

        return redirect()->intended(route('partner.dashboard'))
            ->with('success', "Welcome back, {$partner->name}!");
    }

    /**
     * Log out of the partner portal.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('partner')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('partner.login')->with('success', 'Partner session terminated successfully.');
    }
}