<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
            'featured' => $this->boolean('featured', false),
            'sort_order' => $this->filled('sort_order') ? (int) $this->input('sort_order') : 0,
            'stock' => $this->filled('stock') ? (int) $this->input('stock') : 0,
            'slug' => $this->filled('slug') ? Str::slug($this->input('slug')) : null,
            'sku' => $this->filled('sku') ? strtoupper(trim($this->input('sku'))) : null,
            'sale_price' => $this->filled('sale_price') ? (float) $this->input('sale_price') : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        $productId = $product?->id;

        return [
            'mode_id' => [
                'required',
                'integer',
                Rule::exists('modes', 'id'),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id'),
                function ($attribute, $value, $fail) {
                    $modeId = $this->input('mode_id');
                    if ($modeId && $value) {
                        $category = Category::find($value);
                        if ($category && (int) $category->mode_id !== (int) $modeId) {
                            $fail('The selected category does not belong to the chosen shopping mode.');
                        }
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:3072'],
            'status' => ['boolean'],
            'featured' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mode_id.required' => 'Please select a shopping mode.',
            'mode_id.exists' => 'The selected shopping mode is invalid.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'name.required' => 'The product name is required.',
            'slug.unique' => 'This product URL slug is already taken.',
            'sku.unique' => 'This SKU is already in use by another product.',
            'price.required' => 'The product regular price is required.',
            'price.min' => 'The price must be greater than or equal to 0.',
            'sale_price.lte' => 'The sale price must be less than or equal to the regular price.',
            'stock.required' => 'The stock quantity is required.',
            'stock.min' => 'The stock quantity cannot be negative.',
        ];
    }
}
