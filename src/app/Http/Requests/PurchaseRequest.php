<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment_method' => ['required'],
            'shipping_address' => ['required'],
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_method' => '支払い方法',
            'shipping_address' => '配送先',
        ];
    }
    
    public function messages(): array
    {
        return [
            'payment_method.required' => '支払い方法を選択してください。',
            'shipping_address.required' => '配送先を選択してください。',
        ];
    }
}
