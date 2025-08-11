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
    public function store(CommentRequest $request, \App\Models\Product $product = null)
        {
            // どこから来ても product_id が取れるように網を広く
            $productId = $product?->id
                ?? $request->input('product_id')
                ?? $request->route('id')        // route('comments.store', ['id'=>...]) 対応
                ?? $request->query('id');       // /comments?id=... にも対応

            abort_unless($productId, 422, 'product_id is required');

            \App\Models\Comment::create([
                'user_id'    => auth()->id(),
                'product_id' => $productId,
                'comment'    => $request->input('comment'),
            ]);

            return back(); // テストは 302 期待
        }

}

