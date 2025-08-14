<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProfileRequest;
use App\Models\Product;

class ProfileController extends Controller
{
    // マイページ表示
    public function mypage()
    {
        $user = Auth::user();

        // 購入した商品を取得
        $purchasedProducts = Product::where('buyer_id', $user->id)->get();

        // 出品した商品（必要なら）
        $listedProducts = Product::where('user_id', $user->id)->get();

        return view('profile.mypage', compact('user', 'purchasedProducts', 'listedProducts'));
    }
    /**
     * 初回ログイン時のプロフィール設定画面を表示
     */
    public function show()
    {
        $user = Auth::user();
        \Log::debug('ユーザーのavatarの状態', ['avatar' => $user->avatar]);

        return view('profile.check', compact('user'));
    }
    public function create()
    {
        $user = Auth::user();
        return view('profile.setup', compact('user'));
    }

    public function store(ProfileRequest $request)
    {  
    $user = Auth::user();

    if ($request->hasFile('avatar')) {
        $filename = uniqid() . '.' . $request->file('avatar')->getClientOriginalExtension();
        $request->file('avatar')->move(public_path('uploads/avatars'), $filename);
        $user->avatar = 'uploads/avatars/' . $filename;
    }

    $user->zipcode = $request->zipcode;
    $user->address = $request->address;
    $user->building = $request->building;
    $user->is_profile_set = true;
    $user->save();

    Auth::login($user->fresh());

    return redirect()->route('products.index');
    }

    /**
     * プロフィール情報を保存し、フラグを立てる
     */
    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

    if ($request->hasFile('avatar')) {
        $filename = uniqid() . '.' . $request->file('avatar')->getClientOriginalExtension();
        $request->file('avatar')->move(public_path('uploads/avatars'), $filename);
        $user->avatar = 'uploads/avatars/' . $filename;
    }

        $user->name = $request->name;
        $user->zipcode = $request->zipcode;
        $user->address = $request->address;
        $user->building = $request->building;
        $user->is_profile_set = true;
        $user->save();

        Auth::login($user);

        return redirect()->route('profile.check')->with('success', 'プロフィールを更新しました');
    }
    /**
     * プロフィール確認画面を表示
     */
    public function check()
        {
            $user = Auth::user();
            return view('profile.check', compact('user'));
        }
    /**
     * 通常のプロフィール編集画面を表示
     */
    // プロフィール編集（edit.blade.php）
    public function edit()
        {
            $user = Auth::user();
            return view('profile.edit', compact('user'));
        }
    /**
     * 通常のプロフィール編集画面を表示
     */
    // 住所変更画面（address.blade.php）
    public function editAddress(Product $product)
    {
        $user = Auth::user();
        $productId = $product->id;
        
        return view('address.address', compact('user', 'product'));
    }

    public function updateAddress(Request $request)
    {
        $request->validate([
            'zipcode' => 'required',
            'address' => 'required',
            'building' => 'nullable',
        ]);

        $user = Auth::user();
        $user->update([
            'zipcode' => $request->zipcode,
            'address' => $request->address,
            'building' => $request->building,
        ]);

        return redirect()->route('address.edit')->with('success', '住所を更新しました');
    }
    /**
     * 住所更新（購入フロー）
     * ルート: address.update.from.purchase
     * {id} はユーザーID
     */
    public function updateFromPurchase(AddressRequest $request, int $id)
    {
        // 本人チェック
        if ($id !== Auth::id()) {
            abort(403);
        }

        $v = $request->validated();

        $user = User::findOrFail($id);
        $user->fill([
            'zipcode'  => $v['zipcode'],
            'address'  => $v['address'],
            'building' => $v['building'] ?? null,
        ])->save();

        // 購入ページへ戻す（hidden の product_id を使う）
        if ($request->filled('product_id')) {
            return redirect()
                ->route('products.purchase', ['product' => $request->input('product_id')])
                ->with('success', '住所を更新しました。');
        }

        return back()->with('success', '住所を更新しました。');
    }


}
