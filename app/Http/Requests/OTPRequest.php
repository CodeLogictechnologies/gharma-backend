<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OTPRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // ✅ FIXED EMAIL RULE
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function messages(): array
    {
        return [

            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'email.exists' => 'Email not found',
        ];
    }

    // ✅ CLEAN ERROR RESPONSE (FIRST ERROR ONLY)
    protected function failedValidation(Validator $validator)
    {
        $firstError = collect($validator->errors()->all())->first();

        throw new HttpResponseException(
            response()->json([
                'type' => 'error',
                'message' => $firstError
            ], 422)
        );
    }
}