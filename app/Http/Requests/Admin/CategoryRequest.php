<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status', true),
            'is_featured' => $this->boolean('is_featured', false),
            'sort_order' => $this->filled('sort_order') ? (int) $this->input('sort_order') : 0,
            'slug' => $this->filled('slug') ? Str::slug($this->input('slug')) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Category|null $category */
        $category = $this->route('category');
        $categoryId = $category?->id;

        return [
            'mode_id' => ['nullable', 'integer', Rule::exists('modes', 'id')],
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:160',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                function ($attribute, $value, $fail) use ($categoryId, $category) {
                    if ($categoryId && (int) $value === (int) $categoryId) {
                        $fail('A category cannot be set as its own parent.');
                    }

                    if ($categoryId && $category && in_array((int) $value, $category->getDescendantIds(), true)) {
                        $fail('A category cannot select one of its own subcategories as parent.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:3072'],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'status' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:600'],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'parent_id' => 'parent category',
            'sort_order' => 'sort order',
            'status' => 'status',
        ];
    }
}
