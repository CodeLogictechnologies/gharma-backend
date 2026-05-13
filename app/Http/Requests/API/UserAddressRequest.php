<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();

            $profile = $payload->get('profile');
            $this->merge([
                'orgid'  => $profile['orgid'],
                'userid' => $profile['userid'],
            ]);
        } catch (\Exception $e) {
            throw new HttpResponseException(
                response()->json([
                    'status'  => false,
                    'message' => 'Invalid or expired token.',
                ], 401)
            );
        }
    }

    public function rules(): array
    {
        return [
            'orgid' => 'required|uuid|exists:organizations,id',
            'userid' => 'nullable|uuid|exists:users,id',

            'title' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',

            'address_name' => 'required|string|max:255',
            'other_address_name' => 'nullable|string|max:255',

            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

            'type' => 'required|in:home,work,other,campus',

            'status' => 'nullable|in:Y,N',
        ];
    }

    public function messages(): array
    {
        return [
            'orgid.required' => 'Organization is required',
            'address_name.required' => 'Address is required',
            'type.required' => 'Address type is required',
        ];
    }

    // 🔥 THIS FIXES YOUR ISSUE
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