<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            Fortify::username() => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only(Fortify::username(), 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                Fortify::username() => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if (!$user->is_profile_set) {
            return redirect()->route('profile.setup');
        }

        return redirect()->route('products.index');
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'ログアウトしました');
    }
}
