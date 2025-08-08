<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $keyword = $request->input('keyword');
        $onlyLiked = $request->input('mylist') === '1';

        $productsQuery = Product::query();

        // 自分の商品を除外
        if ($user) {
            $productsQuery->where('user_id', '!=', $user->id);
        }

        // 検索がある場合
        if ($keyword) {
            $productsQuery->where('name', 'like', "%{$keyword}%");
        }

        // マイリストタブ＋ログイン済なら、likesに絞る
        if ($onlyLiked && $user) {
            $likedProductIds = $user->likes()->pluck('product_id')->toArray();
            $productsQuery->whereIn('id', $likedProductIds);
        }

        $products = $productsQuery->get();

        return view('products.index', compact('products', 'onlyLiked', 'keyword'));
    }


}
