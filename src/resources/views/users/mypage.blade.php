@extends('layouts.authenticated')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<script src="{{ asset('js/image-preview.js') }}"></script>

{{-- パンくず --}}
<div class="breadcrumb">
    <a href="{{ route('products.index') }}">商品一覧</a>
    <span> ＞ <a href="{{ route('mypage.index') }}">マイページ</a></span>
</div>

{{-- プロフィール情報 --}}
<div class="mypage-container">
    <div class="profile-header">
        @if ($user->avatar)
            <img src="{{ asset($user->avatar) }}" alt="アバター" class="avatar">
        @else
            <img src="{{ asset('uploads/avatars/no-image.png') }}" alt="アバター" class="avatar">
        @endif

        <div class="user-info">
            <h2>{{ $user->name }}</h2>
            <a href="{{ route('profile.check') }}" class="edit-button">プロフィールを確認</a>
        </div>
    </div>
</div>

{{-- タブ --}}
<div class="tab-links">
    <a href="{{ route('mypage.index', ['tab' => 'sell']) }}" class="{{ $tab === 'sell' ? 'active' : '' }}">
        出品した商品
    </a>
    <a href="{{ route('mypage.index', ['tab' => 'buy']) }}" class="{{ $tab === 'buy' ? 'active' : '' }}">
        購入した商品
    </a>
</div>

{{-- 商品一覧 --}}
<div class="product-list">
    @forelse ($products as $product)
        <div class="product-card" style="position: relative;">
            <a href="{{ $tab === 'sell' ? route('products.edit', $product->id) : route('products.show', ['item_id' => $product->id]) }}">
                @if ($product->image_path && file_exists(public_path($product->image_path)))
                    <img src="{{ asset($product->image_path) }}" alt="商品画像">
                @else
                    <img src="{{ asset('uploads/products/no-image.png') }}" alt="商品画像">
                @endif

                <p>{{ $product->name }}</p>

                @if ($tab === 'sell' && $product->is_sold)
                    <span class="sold-label">SOLD</span>
                @endif
            </a>
        </div>
    @empty
        
    @endforelse

    {{-- 出品タブだけ出品ボタンを表示 --}}
    @if ($tab === 'sell')
        <a href="{{ route('products.create') }}" class="product-card add-product">
            <div class="product-image">
                <img src="{{ asset('uploads/products/plus.jpeg') }}" alt="新規出品">
            </div>
            <p class="product-name">商品を出品</p>
        </a>
    @endif
</div>
@endsection
