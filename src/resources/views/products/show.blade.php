@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('content')

<script src="{{ asset('js/image-preview.js') }}"></script>

<div class="breadcrumb">
    <a href="{{ route('products.index') }}">商品一覧</a> ＞
    <span>{{ $product->name }}</span>
</div>

<div class="product-detail-container">
    <div class="product-image">
  <div class="product-image-section" style="position: relative;">
    @php
      $mainExists = $product->image_path && Storage::disk('public')->exists($product->image_path);
      $mainUrl = $mainExists ? Storage::url($product->image_path) : asset('images/noimage.png');
    @endphp
    <img id="main-product-image" src="{{ $mainUrl }}" alt="商品画像" width="400">

    @if ($product->is_sold)
      <span class="sold-label">SOLD</span>
    @endif
  </div>

  <h3>その他の画像</h3>
  <div class="thumbnail-list">
    @foreach ($product->productImages as $subImage)
      @php
        $thumbExists = $subImage->image_path && Storage::disk('public')->exists($subImage->image_path);
        $thumbUrl = $thumbExists ? Storage::url($subImage->image_path) : asset('images/noimage.png');
      @endphp
      <img class="thumbnail" src="{{ $thumbUrl }}" alt="サブ画像" width="80" loading="lazy">
    @endforeach
  </div>
</div>


    <div class="product-info-section">
        <h2 class="product-title">{{ $product->name }}</h2>
        <p class="product-brand">{{ $product->brand ?? 'ブランド名' }}</p>
        <p class="product-price">¥{{ number_format($product->price) }} <span class="tax">（税込）</span></p>
        
        <div class="product-icons">
        <form action="{{ route($liked ? 'products.unlike' : 'products.like', $product->id) }}" method="POST" style="display:inline;">
            @csrf
            @if ($liked)
                @method('DELETE')
            @endif
            <button type="submit" class="like-button {{ $liked ? 'liked' : '' }}">★</button>
        </form>
            <span class="icon-count">{{ $product->likes_count ?? 0 }}</span>

            <span class="comment-icon">💬</span>
            <span>{{ $product->comments_count ?? 0 }}</span>
        </div>

        @if (!$product->is_sold)
            <div class="buy-button-a">
                <a href="{{ route('products.purchase', $product->id) }}" class="buy-button">購入手続きへ</a>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <div class="product-description">
            <h3>商品説明</h3>
            <p>{!! nl2br(e($product->description)) !!}</p>
        </div>
        <h3>商品情報</h3>
        <div class="product-meta">
        <p class="product-categories">カテゴリー：
            @foreach($product->categories as $category)
                <span class="category-tag">{{ $category->name }}</span>
            @endforeach
        </p>
            <p>商品の状態：{{ $product->condition ?? '不明' }}</p>
        </div>


        <div class="comments-section">
            <h3>コメント ({{ count($product->comments) }})</h3>

            @foreach ($product->comments as $comment)
                <div class="comment-block">
                    <div class="comment-avatar">
                    <img src="{{ asset($comment->user->avatar ?? 'uploads/avatars/no-image.png') }}" alt="プロフィール画像" class="comment-avatar">

                        
                    </div>
                         <p class="comment-username">{{ $comment->user->name }}</p>    
                </div>
                <div class="comment-content">
                        <p class="comment-text">{{ $comment->body }}</p>
                </div>
            @endforeach
        </div>




        <div class="comments-section">
        <form action="{{ route('comments.store', ['id' => $product->id]) }}" method="POST" class="comment-form">
            @csrf
            <label for="comment" class="comment-label">商品へのコメント</label>
            <textarea name="comment" id="comment" placeholder="コメントを入力してください">{{ old('comment') }}</textarea>
            @error('comment')
            <p class="error-message" style="color:red;">{{ $message }}</p>
            @enderror
            <button type="submit">コメントを送信する</button>
        </form>

        <h3>コメント ({{ $product->comments->count() }})</h3>

        @foreach ($product->comments as $comment)
            <div class="comment-block">
            <div class="comment-avatar">
                <img src="{{ asset($comment->user->avatar ?? 'uploads/avatars/no-image.png') }}"
                    alt="プロフィール画像" class="comment-avatar">
            </div>
            <p class="comment-username">{{ $comment->user->name ?? 'ゲスト' }}</p>
            <div class="comment-content">
                <p class="comment-text">{{ $comment->comment }}</p>
            </div>
            </div>
        @endforeach
        </div>
</div>

@endsection
@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('main-product-image');
    const thumbnails = document.querySelectorAll('.thumbnail');

    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('mouseover', function() {
            mainImage.src = this.src;
        });
    });
});
</script>
@endsection
