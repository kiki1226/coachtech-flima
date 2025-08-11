@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/create.edit.css') }}">
@endsection

@section('content')

{{-- パンくずリスト --}}
<div class="breadcrumb">
    <a href="{{ route('products.index') }}">商品一覧</a> ＞
    <a href="{{ route('mypage.index') }}">マイページ</a> ＞
    <span>{{ $product->name }}</span>
</div>

@if ($errors->any())
            <div class="validation-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
@endif

{{-- 商品編集フォーム --}}
<div class="form-wrapper">
    <h2 class="page-title">商品の編集</h2>
    <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data" class="product-form">
    @csrf
    @method('PUT')

    {{-- 商品画像 --}}
    <div class="form-group-current-image">

        {{-- 左：現在の画像 --}}
        <div class="image-block">
            <label class="form-label">現在の画像</label>
            <div class="current-image">
                <div id="current-carousel-preview-box" data-images='@json($product->productImages->pluck("image_path")->map(fn($path) => asset($path)))'>
                    <img id="current-carousel-preview-img" src="{{ asset($product->productImages->first()->image_path ?? $product->image_path) }}" alt="現在の画像">
                </div>
                <div id="current-carousel-dots"></div>
            </div>
        </div>
        {{-- 右：更新画像 --}}
        <div class="image-upload-block" data-image-preview-set>
            <label class="form-label">更新画像</label>
            <input
                type="file"
                name="images[]"
                id="image-input"
                accept="image/*"
                multiple
                class="image-input hidden-file"
            >

            <div class="carousel-preview-box">
                <img class="image-preview" src="{{ asset($product->image_path) }}" alt="プレビュー画像">
            </div>
            <div class="carousel-dots"></div>
            <label for="image-input" class="custom-file-label">画像を変更する</label>

            @if ($errors->has('images.0'))
                <div class="error">{{ $errors->first('images.0') }}</div>
            @endif
        </div>
    </div>

    {{-- カテゴリー --}}
    <div class="form-group">
            <h2 class="form-subtitle">商品の詳細</h2>
            <label class="form-label">カテゴリー（複数選択可）</label>
            <div class="category-tags">
                @foreach (config('categories') as $id => $name)
                    <label class="category-tag">
                        <input type="checkbox" name="category_ids[]" value="{{ $id }}" hidden>
                        <span>{{ $name }}</span>
                    </label>
                @endforeach
            </div>
            @error('category_ids')
                <div class="validation-errors">{{ $message }}</div>
            @enderror
        </div>

    {{-- 商品の状態 --}}
    <div class="form-group">
        <label class="form-label">商品の状態</label>
        <select name="condition" class="form-control">
            <option value="">選択してください</option>
            <option value="新品" {{ old('condition', $product->condition) == '新品' ? 'selected' : '' }}>新品</option>
            <option value="未使用に近い" {{ old('condition', $product->condition) == '未使用に近い' ? 'selected' : '' }}>未使用に近い</option>
            <option value="目立った傷なし" {{ old('condition', $product->condition) == '目立った傷なし' ? 'selected' : '' }}>目立った傷なし</option>
            <option value="傷や汚れあり" {{ old('condition', $product->condition) == '傷や汚れあり' ? 'selected' : '' }}>傷や汚れあり</option>
        </select>
        @error('condition')
            <div class="validation-errors">{{ $message }}</div>
        @enderror
    </div>

    <h2 class="form-subtitle">商品名と説明</h2>

    {{-- 商品名 --}}
    <div class="form-group">
        <label class="form-label">商品名</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}">
    </div>

    {{-- ブランド --}}
    <div class="form-group">
        <label class="form-label">ブランド名</label>
        <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
        @error('name')
            <div class="validation-errors">{{ $message }}</div>
        @enderror
    </div>

    {{-- 説明 --}}
    <div class="form-group">
        <label class="form-label">商品の説明</label>
        <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
        @error('description')
            <div class="validation-errors">{{ $message }}</div>
        @enderror
    </div>

    {{-- 価格 --}}
    <div class="form-group">
        <label class="form-label">販売価格</label>
        <input type="text" name="price" class="form-control" value="{{ old('price', $product->price) }}">
        @error('price')
            <div class="validation-errors">{{ $message }}</div>
        @enderror
    </div>

    {{-- ボタン --}}
    <div class="form-group center" style="display: flex; gap: 10px; justify-content: center;">
        <button type="submit" name="action" value="update" class="submit-button">更新する</button>
    
    </form>
    <form method="POST" action="{{ route('products.destroy', $product->id) }}" onsubmit="return confirm('本当に削除しますか？')">
    @csrf
    @method('DELETE')
        <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">削除する</button>
    </div>
</form>
</div>

@endsection

@section('js')
<script src="{{ asset('js/image-preview.js') }}"></script>
@endsection
