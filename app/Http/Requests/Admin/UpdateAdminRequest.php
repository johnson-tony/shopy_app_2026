<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $actor = auth('admin')->user();
        $target = $this->route('admin');

        if (!$actor) {
            return false;
        }

        if ($target instanceof Admin && !$target->canBeModifiedBy($actor)) {
            return false;
        }

        return $actor->isSuperAdmin() || $actor->hasPermission('admins.edit') || $actor->hasPermission('manage-admins');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $adminId = $this->route('admin')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($adminId)],
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
            $target = $this->route('admin');
            $targetRole = Role::find($this->input('role_id'));

            // Cannot downgrade Super Admin unless actor is Super Admin
            if ($target instanceof Admin && $target->isSuperAdmin() && $targetRole && $targetRole->slug !== 'super-admin' && !$actor->isSuperAdmin()) {
                $validator->errors()->add('role_id', 'Only Super Administrators can change the role of a Super Administrator.');
            }

            // Cannot escalate to Super Admin unless actor is Super Admin
            if ($targetRole && $targetRole->slug === 'super-admin' && !$actor->isSuperAdmin()) {
                $validator->errors()->add('role_id', 'Only a Super Administrator can assign the Super Admin role.');
            }
        });
    }
}
