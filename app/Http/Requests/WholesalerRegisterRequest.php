<?php

namespace App\Http\Requests;

class WholesalerRegisterRequest extends BaseRegisterRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'company_name'        => 'nullable|string|max:255',
            'pan_number'          => 'nullable|string|max:50',
            'pan_image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tax_number'          => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
        ]);
    }
}
