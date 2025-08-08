@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="login-container">
    <p class="message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="button">認証はこちらから</button>
    </form>

    <p class="resend-link">
        <a href="{{ route('verification.send') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="resend-button">
            認証メールを再送する
        </a>
    </p>
</div>
@endsection
