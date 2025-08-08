@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')

<div class="address-change-container">
    <h2>住所の変更</h2>

    <form action="{{ route('address.update.purchase', ['product' => $product->id]) }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="zipcode">郵便番号</label>
            <input type="text" name="zipcode" value="{{ old('zipcode', $user->zipcode) }}">
        </div>

        <div class="form-group">
            <label for="address">住所</label>
            <input type="text" name="address" value="{{ old('address', $user->address) }}">
        </div>

        <div class="form-group">
            <label for="building">建物名</label>
            <input type="text" name="building" value="{{ old('building', $user->building) }}">
        </div>

        <button type="submit" class="update-btn">更新する</button>
    </form>
</div>

@endsection
