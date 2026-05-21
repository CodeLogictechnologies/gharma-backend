<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
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
        'name'      => 'required|string|min:3|max:255',
        'phone'     => 'required|string|max:20',
        'email'     => 'required|email|max:255',
        'address'   => 'required|string|max:255',
        'city'      => 'required|string|max:100',
        'country'   => 'required|string|max:100',
        'latitude'  => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ];
}

public function messages(): array
{
    return [
        'name.required'     => 'Store name is required.',
        'name.min'          => 'Store name must be at least 3 characters.',
        'name.max'          => 'Store name must not exceed 255 characters.',
        'phone.required'    => 'Phone number is required.',
        'phone.max'         => 'Phone number must not exceed 20 characters.',
        'email.required'    => 'Email is required.',
        'email.email'       => 'Please enter a valid email address.',
        'address.required'  => 'Address is required.',
        'city.required'     => 'City is required.',
        'country.required'  => 'Country is required.',
        'latitude.required' => 'Latitude is required.',
        'latitude.numeric'  => 'Latitude must be a valid number.',
        'latitude.between'  => 'Latitude must be between -90 and 90.',
        'longitude.required'=> 'Longitude is required.',
        'longitude.numeric' => 'Longitude must be a valid number.',
        'longitude.between' => 'Longitude must be between -180 and 180.',
    ];
}

}
