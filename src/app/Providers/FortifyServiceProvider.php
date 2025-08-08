<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Http\Request;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // ログインフォーム
        Fortify::loginView(function () {
            return view('auth.login');
        });
        
        // 登録フォーム
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // ユーザー登録処理（アクションクラスを指定するだけ）
        Fortify::createUsersUsing(CreateNewUser::class);

        // パスワードリセット（任意）
        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function ($request) {
            return view('auth.reset-password', ['request' => $request]);
        });

        Fortify::authenticateUsing(function (Request $request) {
        // 認証ロジック（任意でカスタマイズ可）
        });

    }
}
