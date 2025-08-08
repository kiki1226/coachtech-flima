<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommentRequest;

class CommentController extends Controller
{
    /**
     * コメントを保存
     */
    public function store(CommentRequest $request, $productId)
    {
        Comment::create([
            'product_id' => $productId,
            'user_id' => auth()->id(),
            'body' => $request->input('comment'), // 'comment' がバリデーションと一致！
        ]);

        return back()->with('success', 'コメントを投稿しました');
    }
}

