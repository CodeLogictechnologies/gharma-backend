<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'id' => ['nullable', 'string'],

            /*
            |--------------------------------------------------------------------------
            | Discount Type
            |--------------------------------------------------------------------------
            */
            'type' => ['required', 'in:percentage,fixed'],

            'percentage' => [
                'nullable',
                'numeric',
                'min:1',
                'max:100',
                'required_if:type,percentage',
            ],

            'value' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:type,fixed',
            ],

            /*
            |--------------------------------------------------------------------------
            | Applies To
            |--------------------------------------------------------------------------
            */
            'applies_to' => [
                'required',
                'in:category,sub_category,sub_sub_category,brand',
            ],

            'category_target_id' => [
                'nullable',
                'required_if:applies_to,category',
            ],

            'sub_category_target_id' => [
                'nullable',
                'required_if:applies_to,sub_category',
            ],

            'sub_sub_category_target_id' => [
                'nullable',
                'required_if:applies_to,sub_sub_category',
            ],

            'brand_target_id' => [
                'nullable',
                'required_if:applies_to,brand',
            ],

            /*
            |--------------------------------------------------------------------------
            | Items
            |--------------------------------------------------------------------------
            */
            'item_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'item_ids.*' => [
                'required',
                'string',
                'distinct',
            ],

            /*
            |--------------------------------------------------------------------------
            | Minimum Requirement
            |--------------------------------------------------------------------------
            */
            'min_requirement' => [
                'nullable',
                'in:none,purchase,quantity',
            ],

            'min_value' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:min_requirement,purchase',
                'required_if:min_requirement,quantity',
            ],

            /*
            |--------------------------------------------------------------------------
            | Usage Limit
            |--------------------------------------------------------------------------
            */
            'usage_limit_type' => [
                'nullable',
                'in:once,limited,per_user',
            ],

            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
                'required_if:usage_limit_type,limited',
            ],

            'usage_limit_per_user' => [
                'nullable',
                'integer',
                'min:1',
                'required_if:usage_limit_type,per_user',
            ],

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */
            'starts_at' => [
                'required',
                'date',
            ],

            'ends_at' => [
                'required',
                'date',
                'after_or_equal:starts_at',
            ],
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if (empty($this->item_ids)) {
                return;
            }

            $orgId = session('orgid'); // or session()->get('orgid')

            $query = DB::table('discount_details as dd')
                ->join('discount_masters as dm', 'dm.id', '=', 'dd.discount_master_id')
                ->where('dm.orgid', $orgId)
                ->where('dm.status', 'Y')
                ->whereIn('dd.variation_id', $this->item_ids);

            // Ignore current record while editing
            if (!empty($this->id)) {
                $query->where('dm.id', '!=', $this->id);
            }

            $exists = $query->exists();

            if ($exists) {
                $validator->errors()->add(
                    'item_ids',
                    'One or more selected items already have a discount.'
                );
            }
        });
    }


    public function messages(): array
    {
        return [

            'type.required' => 'Please select a discount type.',
            'type.in' => 'Invalid discount type.',

            'percentage.required_if' => 'Please enter the discount percentage.',
            'percentage.min' => 'Percentage must be at least 1.',
            'percentage.max' => 'Percentage cannot exceed 100.',

            'value.required_if' => 'Please enter the discount amount.',
            'value.min' => 'Discount amount cannot be negative.',

            'applies_to.required' => 'Please select Applies To.',
            'applies_to.in' => 'Invalid Applies To value.',

            'category_target_id.required_if' => 'Please select a category.',
            'sub_category_target_id.required_if' => 'Please select a sub category.',
            'sub_sub_category_target_id.required_if' => 'Please select a sub sub category.',
            'brand_target_id.required_if' => 'Please select a brand.',

            'item_ids.required' => 'Please select at least one item.',
            'item_ids.array' => 'Invalid item selection.',
            'item_ids.min' => 'Please select at least one item.',

            'item_ids.*.required' => 'Please select an item.',
            'item_ids.*.distinct' => 'Duplicate items are not allowed.',

            'min_value.required_if' => 'Please enter the minimum value.',

            'usage_limit.required_if' => 'Please enter the usage limit.',
            'usage_limit.min' => 'Usage limit must be at least 1.',

            'usage_limit_per_user.required_if' => 'Please enter the per-user usage limit.',
            'usage_limit_per_user.min' => 'Per-user usage limit must be at least 1.',

            'starts_at.required' => 'Please select the start date.',
            'starts_at.date' => 'Invalid start date.',

            'ends_at.required' => 'Please select the end date.',
            'ends_at.date' => 'Invalid end date.',
            'ends_at.after_or_equal' => 'End date must be after or equal to the start date.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'type'    => 'validation',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
