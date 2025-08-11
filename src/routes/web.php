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
*/

// 誰でも
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
})->middleware('guest')->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/email/verify', fn () => view('auth.verify-email'))->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    Auth::loginUsingId($request->user()->id);
    return !$request->user()->is_profile_set ? redirect()->route('profile.setup') : redirect()->route('products.index');
})->middleware('signed')->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました');
})->middleware(['auth','throttle:6,1'])->name('verification.send');

Route::get('/search', [SearchController::class, 'index'])->name('search');

// コメント投稿（テスト仕様に合わせて param ありの1本だけ）
Route::post('/products/{id}/comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('comments.store');

// ログイン必須（プロフィール未設定含む）
Route::middleware('auth')->group(function () {
    // 初回プロフィール設定
    Route::get('/profile/setup',  [ProfileController::class, 'create'])->name('profile.setup');
    Route::post('/profile/setup', [ProfileController::class, 'store'])->name('profile.store');

    // 出品
    Route::get('/sell',               [ProductController::class, 'create'])->name('products.create');
    Route::post('/products',          [ProductController::class, 'store'])->name('products.store');
    Route::get('/sell/{item_id}/edit',[ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}',      [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}',   [ProductController::class, 'destroy'])->name('products.destroy');

    // 購入フロー
    Route::get('/products/{product}/purchase', [ProductController::class, 'purchase'])->name('products.purchase');
    Route::post('/purchase/redirect', [PurchaseController::class, 'redirectToStripe'])->name('purchase.redirect'); // ← ここだけに1本

    // 購入画面からの住所変更
    // 住所変更画面（購入フローから開く）
    Route::get('/purchase/address/{product}', [ProfileController::class, 'editAddress'])
        ->name('purchase.address.edit');
    // 更新（通常）
    Route::put('/address/{id}', [AddressController::class, 'update'])
        ->name('address.update');
    // 更新（購入フローに戻す用）
    Route::put('/address/update/{id}', [AddressController::class, 'updateFromPurchase'])
        ->name('address.update.from.purchase');
            
});
// ログイン+メール認証必須
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');

    Route::get('/mypage/profile/check', [ProfileController::class, 'check'])->name('profile.check');
    Route::get('/mypage/profile/edit',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile/update',[ProfileController::class, 'update'])->name('profile.update');

    // いいね
    Route::post('/products/{product}/like',   [LikeController::class, 'like'])->name('products.like');
    Route::delete('/products/{product}/like', [LikeController::class, 'unlike'])->name('products.unlike');
    Route::get('/mypage/likes',               [LikeController::class, 'index'])->name('likes.index');
});

// 一覧・詳細
Route::get('/',               [ProductController::class, 'index'])->name('products.index');
Route::get('/item/{item_id}', [ProductController::class, 'show'])->name('products.show');

// 購入完了
Route::get('/complete/{id}', [PurchaseController::class, 'complete'])->name('purchase.complete');

// いいね一括
Route::post('/products/bulk-like', [LikeController::class, 'bulkLike'])->middleware('auth')->name('products.bulk-like');

// リダイレクト
Route::redirect('/products', '/');
Route::redirect('/products/{id}/edit', '/sell/{id}/edit');
Route::redirect('/mypage/profile', '/mypage/profile/check');