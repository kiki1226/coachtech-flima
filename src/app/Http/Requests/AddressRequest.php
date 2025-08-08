<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
            'name' => ['required'],
            'zipcode' => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'address' => ['required'],
            'building' => ['nullable'],
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'お名前',
            'zipcode' => '郵便番号',
            'address' => '住所',
            'building' => '建物名・部屋番号',
        ];
    }
public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください。',
            'zipcode.required' => '郵便番号を入力してください。',
            'zipcode.regex' => '郵便番号は「123-4567」の形式で入力してください。',
            'address.required' => '住所を入力してください。',
        ];
    }
}
