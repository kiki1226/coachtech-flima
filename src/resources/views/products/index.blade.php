@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/products.css') }}">
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

<script src="{{ asset('js/image-preview.js') }}"></script>

<div class="breadcrumb">
    <a href="{{ route('products.index') }}">商品一覧</a> ＞
</div>

<div class="tab-menu">
    <a href="{{ route('products.index', ['keyword' => request('keyword')]) }}" class="{{ !$onlyLiked ? 'active' : '' }}">おすすめ</a>
    <a href="{{ route('products.index', ['keyword' => request('keyword'), 'mylist' => 1]) }}" class="{{ $onlyLiked ? 'active' : '' }}">マイリスト</a>
</div>


<div class="product-list">
  @forelse ($products as $product)
    <div class="product-item" style="position: relative;">
      <div class="image-wrapper">
        @php
            $ok = $product->image_path && Storage::disk('public')->exists($product->image_path);
            $url = $ok ? Storage::url($product->image_path) : asset('images/noimage.png');
        @endphp
            <img src="{{ $url }}" alt="{{ $product->name }}" loading="lazy">


        @if ($product->is_sold)
          <span class="sold-label">SOLD</span>
        @endif
      </div>

      <p class="product-name">{{ $product->name }}</p>
      <p class="product-price">¥{{ number_format($product->price) }}</p>
      <a href="{{ route('products.show', ['item_id' => $product->id]) }}" class="detail-link">詳細を見る</a>
    </div>
  @empty
    <p class="empty">該当する商品はありません。</p>
  @endforelse
</div>
@endsection
