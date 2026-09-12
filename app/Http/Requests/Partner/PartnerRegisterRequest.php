<?php

namespace App\Http\Requests\Partner;

use App\Models\DeliveryPartner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PartnerRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['required', 'string', 'email', 'max:150', 'unique:delivery_partners,email'],
            'phone'           => ['required', 'string', 'min:10', 'max:20'],
            'password'        => ['required', 'confirmed', Password::defaults()],
            'vehicle_type'    => ['required', 'string', 'in:bike,motorcycle,scooter,bicycle,ev,van,other'],
            'vehicle_number'  => ['nullable', 'string', 'max:30'],
            'modes'           => ['required', 'array', 'min:1'],
            'modes.*'         => ['exists:modes,id'],
            'license_number'  => ['nullable', 'string', 'max:50'],
            'license_image'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'id_proof_type'   => ['nullable', 'string', 'in:aadhaar,pan,voter_id,passport'],
            'id_proof_number' => ['nullable', 'string', 'max:50'],
            'id_proof_image'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_ifsc'       => ['nullable', 'string', 'max:20'],
            'upi_id'          => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'modes.required' => 'Please select at least one shopping delivery channel (Shopy, Minutes, or Food).',
            'email.unique'   => 'An account with this email address is already registered as a delivery partner.',
        ];
    }
}
