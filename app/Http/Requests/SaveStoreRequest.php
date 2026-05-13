<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveStoreRequest extends FormRequest
{
    /**
     * Allow request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:20',
            'email'     => 'required|email|max:255',

            'address'   => 'required|string|max:255',
            'city'      => 'required|string|max:100',
            'country'   => 'required|string|max:100',

            // 'latitude'  => 'nullable|numeric|between:-90,90',
            // 'longitude' => 'nullable|numeric|between:-180,180',

            // 'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Custom messages (optional)
     */
    public function messages(): array
    {
        return [
            'name.required'    => 'Store name is required.',
            'name.string'      => 'Store name must be a valid text.',
            'name.max'         => 'Store name must not exceed 255 characters.',

            'phone.required'   => 'Phone number is required.',
            'phone.string'     => 'Phone number must be valid text.',
            'phone.max'        => 'Phone number must not exceed 20 characters.',

            'email.required'   => 'Email is required.',
            'email.email'      => 'Please enter a valid email address.',
            'email.max'        => 'Email must not exceed 255 characters.',

            'address.required' => 'Address is required.',
            'address.string'   => 'Address must be valid text.',
            'address.max'      => 'Address must not exceed 255 characters.',

            'city.required'    => 'City is required.',
            'city.string'      => 'City must be valid text.',
            'city.max'         => 'City must not exceed 100 characters.',

            'country.required' => 'Country is required.',
            'country.string'   => 'Country must be valid text.',
            'country.max'      => 'Country must not exceed 100 characters.',
        ];
    }
}