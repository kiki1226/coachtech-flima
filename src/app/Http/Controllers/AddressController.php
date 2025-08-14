<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User;
use App\Http\Requests\AddressRequest;

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
    public function update(\App\Http\Requests\AddressRequest $request, int $id)
    {
        if ($id !== \Auth::id()) abort(403);

        $user = \App\Models\User::findOrFail($id);
        $user->fill([
            'zipcode'  => $request->input('zipcode'),
            'address'  => $request->input('address'),
            'building' => $request->input('building'),
        ])->save();

        // ★ integer() は未サポート環境があるのでキャストでOK
        $productId = (int) $request->input('product_id', 0);

        if ($productId > 0) {
            return redirect()
                ->route('products.purchase', ['product' => $productId])
                ->with('success', '住所を更新しました。');
        }

        // ★ ここを back() に戻す（名前付きルート不要）
        return back()->with('success', '住所を更新しました。');
    }

    // 購入フロー内での住所更新（$id は product のID）
    public function updateFromPurchase(AddressRequest $request, int $id)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $user->zipcode  = $validated['zipcode'];
        $user->address  = $validated['address'];
        $user->building = $validated['building'] ?? null;
        $user->save();

        return $this->update($request, $id);
    }
    
}
