<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

class AddressController extends Controller
{
    public function edit(Product $product)
    {
        $user = Auth::user();
        return view('address.edit', [
            'user' => $user,
            'product' => $product
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $user = Auth::user();

        $request->validate([
            'zipcode' => 'required',
            'address' => 'required',
            'building' => 'nullable',
        ]);

        $user->update([
            'zipcode' => $request->zipcode,
            'address' => $request->address,
            'building' => $request->building,
        ]);

        return redirect()->route('products.purchase', ['product' => $product->id])
                         ->with('success', '住所を更新しました');
    }
}
