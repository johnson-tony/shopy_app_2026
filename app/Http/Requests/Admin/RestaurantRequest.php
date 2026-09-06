<?php

namespace App\Http\Requests\Admin;

use App\Models\Restaurant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug'          => $this->filled('slug') ? Str::slug($this->input('slug')) : Str::slug($this->input('name')),
            'status'        => $this->boolean('status', true),
            'is_pure_veg'   => $this->boolean('is_pure_veg', false),
            'is_featured'   => $this->boolean('is_featured', false),
            'delivery_time' => $this->filled('delivery_time') ? (int) $this->input('delivery_time') : 30,
            'cost_for_two'  => $this->filled('cost_for_two') ? (float) $this->input('cost_for_two') : 300.00,
            'rating'        => $this->filled('rating') ? (float) $this->input('rating') : 4.5,
            'ratings_count' => $this->filled('ratings_count') ? (int) $this->input('ratings_count') : 100,
        ]);
    }

    public function rules(): array
    {
        /** @var Restaurant|null $restaurant */
        $restaurant = $this->route('restaurant');
        $restaurantId = $restaurant?->id;

        return [
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', Rule::unique('restaurants', 'slug')->ignore($restaurantId)],
            'cuisine'       => ['required', 'string', 'max:255'],
            'delivery_time' => ['required', 'integer', 'min:5', 'max:180'],
            'cost_for_two'  => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'rating'        => ['nullable', 'numeric', 'min:0', 'max:5'],
            'ratings_count' => ['nullable', 'integer', 'min:0'],
            'address'       => ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'is_pure_veg'   => ['boolean'],
            'is_featured'   => ['boolean'],
            'status'        => ['boolean'],
            'image'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:3072'],
            'banner_image'  => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Restaurant name is required.',
            'slug.unique'      => 'This restaurant URL slug is already taken.',
            'cuisine.required' => 'Cuisine type is required (e.g., South Indian, Biryani).',
        ];
    }
}
