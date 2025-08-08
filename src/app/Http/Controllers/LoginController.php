<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    public function showLoginForm()
        {
            return view('auth.login');
        }

    public function login(LoginRequest $request)
        {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                if (!Auth::user()->is_profile_set) {
                    return redirect()->route('profile.setup'); // ← プロフィール設定画面
                }
                
                return redirect()->intended('/products')->with('success', 'ログインしました');
            }

            return back()->withErrors([
                'email' => 'メールアドレスまたはパスワードが正しくありません',
            ])->onlyInput('email');
        }

    public function logout(Request $request)
        {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->with('success', 'ログアウトしました');
        }
}
