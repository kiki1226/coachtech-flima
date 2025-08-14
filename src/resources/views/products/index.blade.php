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
            // use 文は不要。Storage はファサード別名でそのまま呼べます
            $path = $product->image_path;
            $url  = asset('images/noimage.png'); // デフォルト画像

            if ($path) {
                // ← ここでグローバルヘルパーを呼ぶだけ（Blade内で関数定義しない）
                $publicKey = normalizePublicPath($path); // "storage/..." → "uploads/..."

                if ($publicKey && Storage::disk('public')->exists($publicKey)) {
                    // storage/app/public にある（アップロード画像）
                    $url = Storage::url($publicKey);     // => /storage/...
                } elseif (file_exists(public_path($path))) {
                    // public直下（fixtures等）
                    $url = asset($path);                 // => /uploads/...
                }
            }
        @endphp

    <img src="{{ $url }}" alt="{{ $product->name }}">

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
