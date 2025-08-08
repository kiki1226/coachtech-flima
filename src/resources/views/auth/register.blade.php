@extends('layouts.app')

@section('content')
<div class="register-container">
    <div class="register-box">
        <h2 class="h2">会員登録</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            @if ($errors->any())
                <div class="error-summary">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ユーザー名 --}}
            <div class="form-group">
                <label class="label">ユーザー名</label>
                <input type="text" name="name" value="{{ old('name') }}">
                @error('name')
                    <div class="error-box">{{ $message }}</div>
                @enderror
            </div>


            {{-- メールアドレス --}}
            <div class="form-group">
                <label class="label">メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <div class="error-box">{{ $message }}</div>
                @enderror
            </div>

            {{-- パスワード --}}
            <div class="form-group">
                <label class="label">パスワード</label>
                <input type="password" name="password">
                @error('password')
                    <div class="error-box">{{ $message }}</div>
                @enderror
            </div>

            {{-- パスワード確認 --}}
            <div class="form-group">    
                <label class="label">確認用パスワード</label>
                <input type="password" name="password_confirmation">
                @error('password_confirmation')
                    <div class="error-box">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-text">
                <button type="submit" class="register-button">登録する</button>
            </div>
        </form>

        <p class="login-link">
            <a href="{{ route('login') }}">ログインはこちら</a>
        </p>
    </div>
</div>
@endsection
