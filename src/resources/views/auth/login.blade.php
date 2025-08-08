@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')


<div class="login-container">
    <h2>ログイン</h2>

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" >
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label>パスワード</label>
            <input type="password" name="password" >
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">ログイン</button>
    </form>
        <p class="login-link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </p>
</div>
@endsection
