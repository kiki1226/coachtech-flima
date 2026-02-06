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
        'zipcode'  => ['required','string','regex:/^\d{3}-\d{4}$/'],
        'address'  => ['required','string','max:255'],
        'building' => ['nullable','string','max:255'],
        'product_id' => ['nullable','integer','exists:products,id'],
        ];
    }
    
    protected function prepareForValidation(): void
    {
        // 前後空白を除去（全角スペースも）
        $trim = fn($v) => is_string($v) ? preg_replace('/^[\h\s\x{3000}]+|[\h\s\x{3000}]+$/u', '', $v) : $v;

        $zip = preg_replace('/\D/u', '', (string) $this->input('zipcode')); // 数字以外除去
        if (strlen($zip) === 7) {
            $zip = substr($zip, 0, 3) . '-' . substr($zip, 3);
        }

        $this->merge([
            'zipcode'  => $zip,
            'address'  => $trim($this->input('address')),
            'building' => $trim($this->input('building')),
        ]);
    }

    public function attributes(): array
    {
        return [
            'zipcode' => '郵便番号',
            'address' => '住所',
            'building' => '建物名・部屋番号',
        ];
    }

    public function messages(): array
    {
        return [
            'zipcode.required' => '郵便番号を入力してください。',
            'zipcode.regex' => '郵便番号は「123-4567」の形式で入力してください。',
            'address.required' => '住所を入力してください。',
        ];
    }
}
