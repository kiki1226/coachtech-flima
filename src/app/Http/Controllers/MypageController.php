<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product; 

class MypageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'sell'); // デフォルトは出品商品

        if ($tab === 'buy') {
            // 購入した商品
            $products = Product::where('buyer_id', $user->id)->latest()->take(10)->get();
        } else {
            // 出品した商品
            $products = Product::where('user_id', $user->id)->latest()->take(10)->get();
        }

        return view('users.mypage', [
            'user' => $user,
            'products' => $products,
            'tab' => $tab,
        ]);
    }
    public function show()
    {
        $user = Auth::user();
        return view('users.mypage', compact('user'));
    }
    
}
