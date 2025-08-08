@extends('layouts.app')

@section('content')
<h2>パスワードリセット</h2>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    
    <label for="email">メールアドレス</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
    @error('email')
        <div class="error">{{ $message }}</div>
    @enderror

    <label for="password">新しいパスワード</label>
    <input id="password" type="password" name="password" required>
    @error('password')
        <div class="error">{{ $message }}</div>
    @enderror

    <label for="password_confirmation">パスワード再入力</label>
    <input id="password_confirmation" type="password" name="password_confirmation" required>

    <button type="submit">パスワードをリセット</button>
</form>
@endsection
