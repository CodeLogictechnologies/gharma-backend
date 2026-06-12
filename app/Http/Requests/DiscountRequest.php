<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DiscountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'                   => ['nullable', 'string'],

            // ── Basic ──────────────────────────────────────────────────────
            // 'title'             => ['required', 'string', 'max:255'],   // uncomment if title field is re-added to form
            'type'                 => ['required', 'in:percentage,fixed,coupon'],

            // ── Value fields (conditional on type) ────────────────────────
            'percentage'           => ['nullable', 'numeric', 'min:1', 'max:100',
                                        'required_if:type,percentage'],
            'value'                => ['nullable', 'numeric', 'min:0',
                                        'required_if:type,fixed'],
            'coupon_code'          => ['nullable', 'string', 'max:100',
                                        'required_if:type,coupon'],

            // ── Applies To ─────────────────────────────────────────────────
            'applies_to'           => ['required', 'in:entire,item,variation'],
            'item_id'              => ['nullable', 'string',
                                        'required_if:applies_to,item',
                                        'required_if:applies_to,variation'],
            'variation_id'         => ['nullable', 'string',
                                        'required_if:applies_to,variation'],

            // ── Minimum Requirement ────────────────────────────────────────
            'min_requirement'      => ['nullable', 'in:none,purchase,quantity'],
            'min_value'            => ['nullable', 'numeric', 'min:0',
                                        'required_if:min_requirement,purchase',
                                        'required_if:min_requirement,quantity'],

            // ── Usage Limits ───────────────────────────────────────────────
            'usage_limit_type'     => ['nullable', 'in:once,limited,per_user'],
            'usage_limit'          => ['nullable', 'integer', 'min:1',
                                        'required_if:usage_limit_type,limited'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1',
                                        'required_if:usage_limit_type,per_user'],

            // ── Dates ──────────────────────────────────────────────────────
            'starts_at'            => ['required', 'date'],
            'ends_at'              => ['required', 'date', 'after_or_equal:starts_at'],

            // ── Other ──────────────────────────────────────────────────────
            'discount_type'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            // 'title.required'                    => 'Discount title is required.',

            'type.required'                     => 'Please select a discount type.',
            'type.in'                           => 'Discount type must be Percentage, Fixed Amount, or Coupon.',

            'percentage.required_if'            => 'Percentage value is required.',
            'percentage.min'                    => 'Percentage must be at least 1.',
            'percentage.max'                    => 'Percentage cannot exceed 100.',

            'value.required_if'                 => 'Fixed amount is required.',
            'value.min'                         => 'Amount must be 0 or greater.',

            'coupon_code.required_if'           => 'Coupon code is required.',

            'applies_to.required'               => 'Please select what this discount applies to.',
            'applies_to.in'                     => 'Invalid applies to value.',

            'item_id.required_if'               => 'Please select an item.',
            'variation_id.required_if'          => 'Please select a variation.',

            'min_value.required_if'             => 'Minimum value is required.',

            'usage_limit.required_if'           => 'Please enter total usage limit.',
            'usage_limit.min'                   => 'Usage limit must be at least 1.',

            'usage_limit_per_user.required_if'  => 'Please enter per customer usage limit.',
            'usage_limit_per_user.min'          => 'Per customer limit must be at least 1.',

            'starts_at.required'                => 'Start date is required.',
            'starts_at.date'                    => 'Start date must be a valid date.',

            'ends_at.required'                  => 'End date is required.',
            'ends_at.date'                      => 'End date must be a valid date.',
            'ends_at.after_or_equal'            => 'End date must be on or after the start date.',
        ];
    }
}