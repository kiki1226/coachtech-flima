@extends('layouts.app')

@section('content')
<h2>パスワードをお忘れですか？</h2>

@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <label for="email">メールアドレス</label>
    <input id="email" type="email" name="email" required autofocus>

    @error('email')
        <div class="error">{{ $message }}</div>
    @enderror

    <button type="submit">パスワードリセットリンクを送信</button>
</form>
@endsection
