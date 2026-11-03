<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is handled by the auth:api middleware
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'pan_number'   => 'required|string|max:50',
            'pan_image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'type'    => 'error',
            'message' => $validator->errors()->first(),
        ], 422));
    }
}
