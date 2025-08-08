<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class ProfileRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png'], // 画像のみ、jpeg/png
        ];
    }

    public function attributes(): array
    {
        return [
            'avatar' => 'プロフィール画像',
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.image' => ':attribute は画像ファイルを選択してください。',
            'avatar.mimes' => ':attribute の拡張子は jpeg または png のみ利用可能です。',
        ];
    }
}
