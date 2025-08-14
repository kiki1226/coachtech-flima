<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        // テストやフォームの差異を吸収：body でも comment でも受ける
        $text = $request->input('comment', $request->input('body'));
        $request->merge(['comment' => $text]);

        // バリデーション（空/255超を弾く）
        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:255'],
        ]);

        // リレーション経由で作成（product_id は自動で入る）
        $product->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        // 一般的に 302 リダイレクト
        return redirect()
            ->route('products.show', $product)   // or back()
            ->with('status', 'コメントを投稿しました。');
    }
}
