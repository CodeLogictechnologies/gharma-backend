<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id'                   => ['nullable', 'uuid'],
            // 'userid'               => ['required', 'string'],
            // 'orgid'                => ['nullable', 'string'],

            // ── Basic ──────────────────────────────────────────
            'title'                => ['required', 'string', 'max:255'],
            'type'                 => ['required', 'in:percentage,fixed'],

            // ── Value (one required based on type) ─────────────
            'percentage'           => [
                'nullable',
                'numeric',
                'min:1',
                'max:100',
                'required_if:type,percentage'
            ],
            'value'                => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:type,fixed'
            ],

            // ── Applies To ─────────────────────────────────────
            'applies_to'           => ['required', 'in:entire,item,variation'],
            'item_id'              => [
                'nullable',
                'uuid',
                'exists:items,id',
                'required_if:applies_to,item',
                'required_if:applies_to,variation'
            ],
            'variation_id'         => [
                'nullable',
                'uuid',
                'exists:itemvariations,id',
                'required_if:applies_to,variation'
            ],

            // ── Minimum Requirement ────────────────────────────
            'min_requirement'      => ['nullable', 'in:none,purchase,quantity'],
            'min_value'            => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:min_requirement,purchase',
                'required_if:min_requirement,quantity'
            ],

            // ── Usage Limits ───────────────────────────────────
            'usage_limit_type'     => ['nullable', 'in:once,limited,per_user'],
            'usage_limit'          => [
                'nullable',
                'integer',
                'min:1',
                'required_if:usage_limit_type,limited'
            ],
            'usage_limit_per_user' => [
                'nullable',
                'integer',
                'min:1',
                'required_if:usage_limit_type,per_user'
            ],

            'discount_type'               => ['required'],
            // ── Dates ──────────────────────────────────────────
            'starts_at'            => ['required', 'date'],
            'ends_at'              => ['required', 'date', 'after_or_equal:starts_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'                        => 'Discount title is required.',
            'type.required'                         => 'Discount type is required.',
            'type.in'                               => 'Discount type must be percentage or fixed.',
            'percentage.required_if'                => 'Percentage value is required.',
            'percentage.max'                        => 'Percentage cannot exceed 100.',
            'value.required_if'                     => 'Fixed amount is required.',
            'applies_to.required'                   => 'Please select what this discount applies to.',
            'item_id.required_if'                   => 'Please select an item.',
            'variation_id.required_if'              => 'Please select a variation.',
            'min_value.required_if'                 => 'Minimum value is required.',
            'usage_limit.required_if'               => 'Please enter total usage limit.',
            'usage_limit_per_user.required_if'      => 'Please enter per customer usage limit.',
            'starts_at.required'                    => 'Start date is required.',
            'ends_at.required'                      => 'End date is required.',
            'ends_at.after_or_equal'                => 'End date must be after or equal to start date.',
            'discount_type.required'                    => 'Discount Type is required.',
        ];
    }
}