<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExhibitionRequest extends FormRequest
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
            'name'         => ['required','string','max:100'],
            'description'  => ['required','string','max:255'],

            // 画像は未選択OK（ダミー画像にフォールバックする運用）
            'images'       => ['nullable','array','max:6'],
            'images.*'     => ['image','mimes:jpeg,jpg,png','max:5120'],

            // ← ここを修正：DBのIDを許可
            'category_ids'   => ['required','array','min:1'],
            'category_ids.*' => ['integer','exists:categories,id'],

            'condition'        => ['required', Rule::in(['良好','目立った傷や汚れなし','やや傷や汚れあり','状態が悪い'])],
            'price'        => ['required','integer','min:1'],
            'features'     => ['nullable','string','max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '商品名',
            'description' => '商品説明',
            'images' => '商品画像',
            'images.*' => '商品画像',
            'category_ids' => 'カテゴリー',
            'category_ids.*' => 'カテゴリー',
            'condition' => '商品の状態',
            'price' => '価格',
        ];
    }
    
    public function messages(): array
    {
        return [
            'required' => ':attribute を入力してください。',
            'max' => ':attribute は :max 文字以内で入力してください。',
            'image' => ':attribute は画像ファイルで指定してください。',
            'mimes' => ':attribute は jpeg または png 形式で指定してください。',
            'numeric' => ':attribute は数値で入力してください。',
            'min' => ':attribute は :min 円以上で入力してください。',
            'integer' => ':attribute は整数で入力してください。',
            'array' => ':attribute の形式が不正です。',
            'in' => '選択された :attribute は無効です。',
        ];
    }
}
