<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    LoginController, ProfileController, SearchController, ProductController,
    CommentController, MypageController, AddressController, PurchaseController, LikeController
};
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| 重要：より具体的なルート → 抽象・リダイレクトの順で定義。
| /mypage は GET 1本だけ。/products の POST は index より上、redirect より上。
|--------------------------------------------------------------------------
*/

// ========== 1) 一覧・詳細（公開） ==========
Route::get('/',               [ProductController::class, 'index'])->name('products.index');
Route::get('/item/{item_id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/search',         [SearchController::class, 'index'])->name('search');

// ========== 2) 認証まわり（公開→制御） ==========
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', function (Request $request) {
    $request->validate([
        Fortify::username() => 'required|string',
        'password' => 'required|string',
    ]);
    if (Auth::attempt($request->only(Fortify::username(), 'password'), $request->boolean('remember'))) {
        $request->session()->regenerate();
        $user = Auth::user();
        if (!$user->hasVerifiedEmail()) return redirect()->route('verification.notice');
        if (!$user->is_profile_set)     return redirect()->route('profile.setup');
        return redirect()->route('products.index');
    }
    return back()->withErrors([Fortify::username() => __('auth.failed')]);
})->middleware('guest')->name('login.attempt');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/email/verify', fn () => view('auth.verify-email'))
    ->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    Auth::loginUsingId($request->user()->id);
    return !$request->user()->is_profile_set ? redirect()->route('profile.setup') : redirect()->route('products.index');
})->middleware('signed')->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました');
})->middleware(['auth','throttle:6,1'])->name('verification.send');



// ========== 3) 認証必須（出品系・POST /products をココに！） ==========
Route::middleware('auth')->group(function () {
    // 初回プロフィール設定
    Route::get('/profile/setup',  [ProfileController::class, 'create'])->name('profile.setup');
    Route::post('/profile/setup', [ProfileController::class, 'store'])->name('profile.store');
    
    // コメント（テスト仕様に合わせて param あり）
    Route::post('/products/{product}/comments', [CommentController::class, 'store'])
    ->name('comments.store');

    // 出品フロー
    Route::get('/sell',                [ProductController::class, 'create'])->name('products.create');
    Route::post('/products',           [ProductController::class, 'store'])->name('products.store'); // ← ★ここが肝（上に置く）
    Route::get('/sell/{item_id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}',       [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}',    [ProductController::class, 'destroy'])->name('products.destroy');

    // 購入フロー
    Route::get('/products/{product}/purchase', [ProductController::class, 'purchase'])->name('products.purchase');
    Route::post('/purchase/redirect',          [PurchaseController::class, 'redirectToStripe'])->name('purchase.redirect');

    // 住所更新
    Route::get('/purchase/address/{product}', [ProfileController::class, 'editAddress'])
    ->name('purchase.address.edit');
    Route::put('/address/{id}',              [AddressController::class, 'update'])
    ->name('address.update');
    Route::put('/address/update/{id}',       [AddressController::class, 'updateFromPurchase'])
    ->name('address.update.from.purchase');

    // いいね一括
    Route::post('/products/bulk-like', [LikeController::class, 'bulkLike'])->name('products.bulk-like');
    
    // 購入完了ページ
    Route::get('/purchase/complete/{product}', [PurchaseController::class, 'complete'])
    ->name('purchase.complete');
});



// ========== 4) 認証 + メール認証 必須（/mypage はココに1本だけ） ==========
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index'); // ← GETのみ・1本

    Route::get('/mypage/profile/check', [ProfileController::class, 'check'])->name('profile.check');
    Route::get('/mypage/profile/edit',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile/update',[ProfileController::class, 'update'])->name('profile.update');

    Route::post('/products/{product}/like',   [LikeController::class, 'toggle'])->name('products.like');
    Route::delete('/products/{product}/like', [LikeController::class, 'unlike'])->name('products.unlike');
    Route::get('/mypage/likes',               [LikeController::class, 'index'])->name('likes.index');
});

// ========== 5) リダイレクト（最後に置く：GET専用なのでPOSTと競合しない） ==========
Route::redirect('/products/{id}/edit', '/sell/{id}/edit');
Route::redirect('/mypage/profile', '/mypage/profile/check');
// ※ これ ↓ は誤爆防止のため一旦外す or 必要なら最後に置く
// Route::redirect('/products', '/');
