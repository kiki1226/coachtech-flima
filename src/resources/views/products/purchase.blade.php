@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')

<div class="purchase-wrapper">

    <div class="left-area">

        <div class="product-info">
            <div class="product-image">
                @php
                    $url = toPublicUrl($product->image_path); 
                @endphp
                <img src="{{ $url }}" alt="商品画像" width="400">
            </div>
            <div class="product-details">
                <h3 class="product-name">{{ $product->name }}</h3>
                <p class="product-price">¥{{ number_format($product->price) }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('purchase.redirect') }}">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <div class="payment-method-section">
                <label for="payment_method">支払い方法</label>
                <select name="payment_method" id="payment_method">
                    <option value="">選択してください</option>
                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>クレジットカード</option>
                    <option value="konbini" {{ old('payment_method') == 'konbini' ? 'selected' : '' }}>コンビニ払い</option>
                </select>
                {{-- バリデーションエラー表示 --}}
                @error('payment_method')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="address-block">
                <div class="hasso">
                    <p><strong>配送先</strong></p>
                    <a href="{{ route('purchase.address.edit', ['product' => $product->id]) }}" class="address-edit">変更する</a>
                </div>
                <div class="address-main">
                    <p>〒{{ $user->zipcode }}</p>
                    <p>{{ $user->address }} {{ $user->building }}</p>
                </div>

                {{-- hiddenで配送先を送信 --}}
                <input type="hidden" name="shipping_address" value="{{ $user->zipcode }} {{ $user->address }} {{ $user->building }}">

                {{-- バリデーションエラー表示 --}}
                @error('shipping_address')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

    </div>

    <div class="right-area">
        <table class="summary-table">
            <tr>
                <th>商品代金</th>
                <td>¥{{ number_format($product->price) }}</td>
            </tr>
            <tr>
                <th>支払い方法</th>
                <td class="payment-summary">未選択</td>
            </tr>
        </table>

        <button type="submit" class="purchase-btn">購入する</button>
        </form>
    </div>

</div>

<script>
    const paymentMethod = document.getElementById('payment_method');
    const paymentSummary = document.querySelector('.payment-summary');

    paymentMethod.addEventListener('change', function () {
        if (this.value === 'card') {
            paymentSummary.textContent = 'クレジットカード';
        } else if (this.value === 'konbini') {
            paymentSummary.textContent = 'コンビニ払い';
        } else {
            paymentSummary.textContent = '未選択';
        }
    });
</script>

@endsection
