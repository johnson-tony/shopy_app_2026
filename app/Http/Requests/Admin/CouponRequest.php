<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        $code = $this->filled('code') 
            ? Str::upper(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $this->input('code'))) 
            : null;

        $type = $this->input('type');
        $value = $type === Coupon::TYPE_FREE_DELIVERY ? 0 : $this->input('value');

        $this->merge([
            'code'   => $code,
            'value'  => $value,
            'status' => $this->boolean('status', true),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Coupon|null $coupon */
        $coupon = $this->route('coupon');
        $couponId = $coupon?->id;

        return [
            'code' => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', Rule::in(Coupon::TYPES)],
            'value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') === Coupon::TYPE_PERCENTAGE && (float) $value > 100) {
                        $fail('The discount percentage cannot exceed 100%.');
                    }
                },
            ],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'mode_id' => [
                'nullable',
                'integer',
                Rule::exists('modes', 'id'),
                function ($attribute, $value, $fail) {
                    $admin = auth('admin')->user();
                    if ($value && $admin && !$admin->isSuperAdmin() && !$admin->hasModeAccess((int) $value)) {
                        $fail('You are not authorized to assign this shopping channel mode.');
                    }
                },
            ],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['boolean'],
        ];
    }

    /**
     * Custom attribute names for validation.
     */
    public function attributes(): array
    {
        return [
            'code' => 'coupon promo code',
            'name' => 'coupon title',
            'type' => 'discount type',
            'value' => 'discount value',
            'min_order_amount' => 'minimum order amount',
            'max_discount_amount' => 'maximum discount cap',
            'mode_id' => 'shopping channel',
            'usage_limit' => 'total usage limit',
            'usage_limit_per_user' => 'usage limit per user',
            'starts_at' => 'start date & time',
            'expires_at' => 'expiry date & time',
        ];
    }
}
