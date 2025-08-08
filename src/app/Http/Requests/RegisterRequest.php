<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|same:password',
        ];
    }
    /**
     * Get the custom attribute names for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'お名前',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'password_confirmation' => '確認用パスワード',
        ];
    }
    /**
     * Get the validation error messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute を入力してください。',
            'string' => ':attribute は文字列で入力してください。',
            'email' => ':attribute は「＠」を含めて正しい形式で入力してください。',
            'unique' => ':attribute はすでに使用されています。',
            'max' => ':attribute は :max 文字以内で入力してください。',
            'min' => ':attribute は最低 :min 文字以上で入力してください。',
            'confirmed' => 'パスワードと確認用パスワードが一致しません。',
            'password_confirmation.required' => ':attribute を入力してください。',
            'password_confirmation.same' => ':attribute がパスワードと一致しません。',
        ];
    }
}

