<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Tymon\JWTAuth\Facades\JWTAuth;

class FavouriteSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // ── Runs BEFORE validation ─────────────────────────────────
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
            'orgid'               => ['required', 'uuid', 'exists:organizations,id'],
            'userid'              => ['required', 'uuid', 'exists:users,id'],
            'variationid'              => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'orgid.required'               => 'Organization is required.',
            'orgid.uuid'                   => 'Invalid organization ID.',
            'orgid.exists'                 => 'Organization not found.',

            'userid.required'              => 'User is required.',
            'userid.uuid'                  => 'Invalid user ID.',
            'userid.exists'                => 'User not found.',

            'variationid.required'               => 'Please select an item.',

        ];
    }

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