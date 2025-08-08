@extends('layouts.authenticated')

@section('css')
<link rel="stylesheet" href="{{ asset('css/products.css') }}">
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('products.index') }}">商品一覧</a> ＞ マイリスト
</div>

<h2>マイリスト（いいねした商品）</h2>

<div class="product-list">
    @forelse ($likedProducts as $product)
        <div class="product-item">
            <div class="image-wrapper">
                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}">
                @if ($product->is_sold)
                    <span class="sold-label">SOLD</span>
                @endif
            </div>
            <p class="product-name">{{ $product->name }}</p>
            <p class="product-price">¥{{ number_format($product->price) }}</p>
            <a href="{{ route('products.show', $product->id) }}" class="detail-link">詳細を見る</a>
        </div>
    @empty
        <p>マイリストに商品が登録されていません。</p>
    @endforelse
</div>
@endsection
