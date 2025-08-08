<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\LikeController;
use Laravel\Fortify\Fortify; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ▼ 誰でもアクセス可 ▼

// 会員登録・ログイン
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// ログイン処理
Route::post('/login', function (Request $request) {
    $request->validate([
        Fortify::username() => 'required|string',
        'password' => 'required|string',
    ]);

    if (Auth::attempt($request->only(Fortify::username(), 'password'), $request->boolean('remember'))) {
        $request->session()->regenerate();

        $user = Auth::user();

        // ① メール未認証
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // ② プロフィール未設定
        if (!$user->is_profile_set) {
            return redirect()->route('profile.setup');
        }

        // ③ 条件をすべて満たしている場合は商品一覧へ
        return redirect()->route('products.index');
    }

    return back()->withErrors([
        Fortify::username() => __('auth.failed'),
    ]);
})->middleware(['guest'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// メール認証
Route::get('/email/verify', fn () => view('auth.verify-email'))->middleware('auth')
->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    Auth::loginUsingId($request->user()->id);
    return !$request->user()->is_profile_set 
        ? redirect()->route('profile.setup') 
        : redirect()->route('products.index');
})->middleware(['signed'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 検索
Route::get('/search', [SearchController::class, 'index'])->name('search');

// コメント投稿（ログイン必須）
Route::post('/products/{id}/comments', [CommentController::class, 'store'])
->name('comments.store')->middleware('auth');

// ▼ ログイン必須（プロフィール未設定含む） ▼

Route::middleware('auth')->group(function () {

    // 初回プロフィール設定
    Route::get('/profile/setup', [ProfileController::class, 'create'])->name('profile.setup');
    Route::post('/profile/setup', [ProfileController::class, 'store'])->name('profile.store');

    // 商品出品画面
    Route::get('/sell', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/sell/{item_id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');

    // 購入画面
    Route::get('/products/{product}/purchase', [ProductController::class, 'purchase'])
    ->name('products.purchase');

    // Stripe決済用
    Route::post('/purchase/redirect', [PurchaseController::class, 'redirectToStripe'])
    ->name('purchase.redirect');
   
    // 購入画面からの住所変更
    Route::get('/purchase/address/{product}', [ProfileController::class, 'editAddress'])
    ->name('purchase.address.edit');
    Route::post('/address/update/{product}', [AddressController::class, 'updateFromPurchase'])
    ->name('address.update.purchase');

});

// ▼ ログイン+メール認証必須 ▼

Route::middleware(['auth', 'verified'])->group(function () {

    // マイページ
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');
    
    // プロフィール編集
    Route::get('/mypage/profile/check', [ProfileController::class, 'check'])
    ->name('profile.check');
    Route::get('/mypage/profile/edit', [ProfileController::class, 'edit'])
    ->name('profile.edit');
    Route::post('/mypage/profile/update', [ProfileController::class, 'update'])
    ->name('profile.update');

    
    //　いいねボタン
    Route::post('/products/{product}/like', [LikeController::class, 'like'])
    ->name('products.like');
    Route::delete('/products/{product}/like', [LikeController::class, 'unlike'])
    ->name('products.unlike');
    Route::get('/mypage/likes', [LikeController::class, 'index'])->name('likes.index');

    Route::get('/profile/check', [ProfileController::class, 'show'])->name('profile.check');
});

// ▼ 商品一覧・詳細 ▼
Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/item/{item_id}', [ProductController::class, 'show'])->name('products.show');
Route::post('/purchase/redirect', [PurchaseController::class, 'redirectToStripe'])
->name('purchase.redirect');

// ▼ 購入完了画面 ▼

Route::get('/complete/{id}', [PurchaseController::class, 'complete'])->name('purchase.complete');

//　▼ 購入画面 ▼
Route::get('/profile/address', [ProfileController::class, 'editAddress'])
->name('profile.address.edit');
Route::put('/profile/address', [ProfileController::class, 'updateAddress'])
->name('profile.address.update');
// ▼ いいね一括画面 ▼
Route::post('/products/bulk-like', [LikeController::class, 'bulkLike'])
    ->name('products.bulk-like')
    ->middleware('auth');

// ▼ 商品一覧・詳細 ▼
Route::redirect('/products', '/');
Route::redirect('/products/{id}/edit', '/sell/{id}/edit');

// 商品出品画面
Route::get('/sell', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/sell/{item_id}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
