<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $actor = auth('admin')->user();
        return $actor && ($actor->isSuperAdmin() || $actor->hasPermission('admins.create') || $actor->hasPermission('manage-admins'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'modes' => ['nullable', 'array'],
            'modes.*' => ['integer', 'exists:modes,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $actor = auth('admin')->user();
            $targetRole = Role::find($this->input('role_id'));

            // Prevent privilege escalation: only Super Admin can grant Super Admin role
            if ($targetRole && $targetRole->slug === 'super-admin' && !$actor->isSuperAdmin()) {
                $validator->errors()->add('role_id', 'Only a Super Administrator can assign the Super Admin role.');
            }
        });
    }
}
