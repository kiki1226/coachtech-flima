<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User;

class AddressController extends Controller
{
    public function edit($id, Request $request)
        {
            $user = User::findOrFail($id);
            $productId = $request->query('product_id'); // GETパラメータから取得

            return view('address.edit', [
                'user' => $user,
                'productId' => $productId
            ]);
        }
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'zipcode'    => ['required','string'],
            'address'    => ['required','string'],
            'building'   => ['nullable','string'],
            'product_id' => ['nullable','integer'], // ← 必須ではなくしておく
        ]);

        $user = User::findOrFail($id);
        $user->zipcode  = $validated['zipcode'];
        $user->address  = $validated['address'];
        $user->building = $validated['building'] ?? null;
        $user->save();

        if (!empty($validated['product_id'])) {
            return redirect()->route('products.purchase', ['product' => $validated['product_id']])
                            ->with('success', '住所を更新しました。');
        }
        // product_id が無いときは元画面へ
        return back()->with('success', '住所を更新しました。');
    }
    
}
