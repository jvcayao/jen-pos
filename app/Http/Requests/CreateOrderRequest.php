<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paymentMethods = array_keys(config('payment.methods', []));

        return [
            'payment_method' => ['required', 'string', 'in:'.implode(',', $paymentMethods)],
            'payment_method_2' => ['nullable', 'string', 'in:'.implode(',', $paymentMethods)],
            'amount_1' => ['nullable', 'numeric', 'min:0'],
            'amount_2' => ['nullable', 'numeric', 'min:0'],
            'discount_code' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'The selected payment method is invalid.',
        ];
    }
}
