<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Like;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * いいねする
     */
    public function like($productId)
    {
        $product = Product::findOrFail($productId);

        // 二重登録防止
        if (!$product->likes()->where('user_id', Auth::id())->exists()) {
            $product->likes()->create([
                'user_id' => Auth::id(),
            ]);
        }

        return back();
    }

    /**
     * いいね解除する
     */
    public function unlike($productId)
    {
        $product = Product::findOrFail($productId);

        $product->likes()->where('user_id', Auth::id())->delete();

        return back();
    }
    public function index()
    {
        $likedProducts = auth()->user()->likes()->with('product')->get()->pluck('product');

        return view('likes.index', compact('likedProducts'));
    }
    public function bulkLike(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        $user = auth()->user();

        foreach ($productIds as $productId) {
            $user->likes()->firstOrCreate([
                'product_id' => $productId,
            ]);
        }

        return redirect()->route('likes.index')->with('success', '検索結果の商品をマイリストに追加しました。');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $user = auth()->user();

        // ★自分の出品にはいいね禁止（ここが肝）
        if ($product->user_id === $user->id) {
            return back()->with('error', '自分の商品にはいいねできません。');
        }

        // 既にいいね済みなら外す、未いいねなら付ける
        $already = $product->likes()->where('user_id', $user->id)->exists();

        if ($already) {
            $product->likes()->where('user_id', $user->id)->delete();
        } else {
            $product->likes()->create(['user_id' => $user->id]);
        }

        return back();
    }
    
}
