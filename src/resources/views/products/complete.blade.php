@extends('layouts.authenticated')

@section('css')
<link rel="stylesheet" href="{{ asset('css/complete.css') }}">
@endsection

@section('content')

<h2 class="page-title">ご購入ありがとうございました！</h2>

<div class="complete-wrapper">
    <p class="thanks-message">商品は発送準備に入ります。</p>
    <p class="thanks-sub">またのご利用をお待ちしております。</p>

    <div class="btn-area">
        <a href="{{ route('products.index') }}" class="back-btn">商品一覧へ戻る</a>
        <a href="{{ route('mypage.index') }}" class="mypage-btn">マイページへ</a>
    </div>
</div>

@endsection
