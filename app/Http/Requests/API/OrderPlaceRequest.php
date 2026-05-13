<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderPlaceRequest extends FormRequest
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
            // ── From JWT token (auto-injected above) ───────────
            'orgid'               => ['required', 'uuid', 'exists:organizations,id'],
            'userid'              => ['required', 'uuid', 'exists:users,id'],
            'addressid'              => ['required', 'uuid', 'exists:user_addresses,id'],


            // ── Items Array ────────────────────────────────────
            'items'                => ['required', 'array', 'min:1'],
            'items.*.variation_id' => ['required', 'uuid', 'exists:itemvariations,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.price'        => ['required', 'integer', 'min:1'],



            'total'               => ['required', 'min:1'],
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

            'items.required'                => 'At least one item is required.',
            'items.array'                   => 'Items must be an array.',
            'items.min'                     => 'At least one item is required.',

            'discount_type.required'                => 'At Discount Type is required.',


            'items.*.variation_id.required' => 'Each item must have a variation.',
            'items.*.variation_id.uuid'     => 'Invalid variation ID format.',
            'items.*.variation_id.exists'   => 'One or more variations do not exist.',

            'items.*.quantity.required'     => 'Each item must have a quantity.',
            'items.*.quantity.integer'      => 'Quantity must be a whole number.',
            'items.*.quantity.min'          => 'Quantity must be at least 1.',
            'items.*.quantity.max'          => 'Quantity cannot exceed 100.',

            'items.*.price.required'        => 'Each item must have a price.',
            'items.*.price.integer'         => 'Price must be a number.',
            'items.*.price.min'             => 'Price must be at least 1.',

            'total.required'               => 'Total is required.',
            'total.min'                    => 'Total must be at least 1.',

            'addressid.required'              => 'Address is required.',
            'addressid.uuid'                  => 'Invalid address ID.',
            'addressid.exists'                => 'Address not found.',

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
