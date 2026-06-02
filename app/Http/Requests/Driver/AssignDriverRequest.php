<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class AssignDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ordermasterid' => 'required|exists:order_masters,id',
            'driver_id'     => 'required|exists:users,id',
            'delivery_date'     => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'ordermasterid.required' => 'Order is required.',
            'driver_id.required'     => 'Please select a driver.',
            'driver_id.exists'       => 'Invalid driver selected.',
        ];
    }
}
