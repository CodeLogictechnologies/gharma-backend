<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('gender')) {
            $this->merge([
                'gender' => ucfirst(strtolower(trim($this->gender))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'full_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email',
            'password'   => 'required|string|min:6|confirmed',
            'gender'     => 'required|in:Male,Female,Other',
            'address'    => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'image'      => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'email.unique'       => 'This email is already registered.',
            'gender.in'          => 'Gender must be Male, Female or Other.',
            'image.image'        => 'The image must be a valid image file.',
            'image.max'          => 'The image may not be larger than 2 MB.',
        ];
    }

    // Keep the same error response format your API already uses
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'type'    => 'error',
            'message' => $validator->errors()->first(),
        ], 422));
    }
}
