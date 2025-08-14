<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;

class PurchaseController extends Controller
{
    public function redirectToStripe(PurchaseRequest $request)
    {
        
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentMethod = $request->input('payment_method');
        $productId = $request->input('product_id'); 
        
        $product = Product::findOrFail($productId);

        $session = Session::create([
            'payment_method_types' => $paymentMethod === 'konbini' ? ['konbini'] : ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $product->price 
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.complete', ['product' => $product->id]),
            'cancel_url' => url()->previous(),
        ]);

        return redirect($session->url);
    }

    public function complete(\App\Models\Product $product)
    {
        // ここは更新（UPDATE）だけ。新規作成はしない
        if (auth()->check() && is_null($product->buyer_id)) {
            $product->buyer_id = auth()->id();
            $product->save(); // ← UPDATEになる
        }

        return view('products.complete', compact('product'));
    }
}

