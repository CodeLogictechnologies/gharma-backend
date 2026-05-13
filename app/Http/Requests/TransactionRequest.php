<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'method.required' => 'Method is required',
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
