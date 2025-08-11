@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/create.css') }}">
@endsection

@section('content')

{{-- パンくずリスト --}}
<div class="breadcrumb">
    <a href="{{ route('products.index') }}">商品一覧</a> ＞
    <a href="{{ route('mypage.index') }}">マイページ</a> ＞
    <span>商品の出品</span>
</div>

<div class="form-wrapper">
    <h2 class="page-title">商品の出品</h2>

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="product-form">
        @csrf

        {{-- バリデーションエラー表示 --}}
        @if ($errors->any())
            <div class="validation-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 商品画像 --}}
        <div class="form-group image-upload-block" data-image-preview-set>
            <label class="form-label">商品画像</label>
            <div class="carousel-preview-box">
                <img id="carousel-preview-img" src="" alt="プレビュー画像" class="image-preview" style="display:none;">
            </div>
            <div id="carousel-dots" class="carousel-dots"></div>
            <label for="image-input" class="image-upload-label">画像を選択する</label>
            <input
                type="file"
                id="image-input"
                name="images[]"
                accept="image/*"
                class="hidden-file image-input"
                multiple
            >
            @error('images')
                <div class="text-danger">{{ $message }}</div>
            @enderror
            @error('images.*')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        {{-- カテゴリー（複数選択） --}}
        <div class="form-group">
        <label class="form-label">カテゴリー（複数選択可）</label>
        <div class="category-tags">
            @foreach ($categories as $category)
            <label class="category-tag">
                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }} hidden>
                <span>{{ $category->name }}</span>
            </label>
            @endforeach
        </div>

        @error('category_ids')
            <div class="text-danger">{{ $message }}</div>
        @enderror
        </div>

        {{-- 商品の状態 --}}
        <div class="form-group">
            <label class="form-label">商品の状態</label>
            <select name="condition" class="form-control">
                <option value="">選択してください</option>
                <option value="良好" {{ old('condition') == '良好' ? 'selected' : '' }}>良好</option>
                <option value="目立った傷や汚れなし" {{ old('condition') == '目立った傷や汚れなし' ? 'selected' : '' }}>目立った傷や汚れなし</option>
                <option value="やや傷や汚れあり" {{ old('condition') == 'やや傷や汚れあり' ? 'selected' : '' }}>やや傷や汚れあり</option>
                <option value="状態が悪い" {{ old('condition') == '状態が悪い' ? 'selected' : '' }}>状態が悪い</option>
            </select>
            @error('condition')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>



        {{-- 商品名 --}}
        <div class="form-group">
            <label class="form-label">商品名</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>



        {{-- ブランド名（任意） --}}
        <div class="form-group">
            <label class="form-label">ブランド名</label>
            <input type="text" name="brand" class="form-control" value="{{ old('brand') }}">
        </div>



        {{-- 商品説明 --}}
        <div class="form-group">
            <label class="form-label">商品の説明</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
            @error('description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>



        {{-- 販売価格 --}}
        <div class="form-group">
            <label class="form-label">販売価格</label>
            <input type="text" name="price" class="form-control" placeholder="¥" value="{{ old('price') }}">
            @error('price')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>


        
        {{-- 出品ボタン --}}
        <div class="form-group center">
            <button type="submit" class="submit-button">出品する</button>
        </div>
    </form>
</div>

@endsection

@section('js')
<script src="{{ asset('js/image-preview.js') }}"></script>
@endsection
