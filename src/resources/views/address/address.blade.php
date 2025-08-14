@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')
<div class="address-change-container">
  <h2>住所の変更</h2>

  <form
      dusk="purchase-form"
      action="{{ route('address.update.from.purchase', ['id' => $user->id]) }}"
      method="POST">
    @csrf
    @method('PUT')

    {{-- ★ これをフォーム内に置く：購入ページへ戻すための product_id --}}
    <input type="hidden" name="product_id" value="{{ $product->id }}">

    <div class="form-group">
      <label for="zipcode">郵便番号</label>
      <input id="zipcode" dusk="zipcode" type="text" name="zipcode" value="{{ old('zipcode', $user->zipcode) }}">
      @error('zipcode') <p class="error-message" style="color:red;">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
      <label for="address">住所</label>
      <input id="address" dusk="address" type="text" name="address" value="{{ old('address', $user->address) }}">
      @error('address') <p class="error-message" style="color:red;">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
      <label for="building">建物名</label>
      <input id="building" dusk="building" type="text" name="building" value="{{ old('building', $user->building) }}">
      @error('building') <p class="error-message" style="color:red;">{{ $message }}</p> @enderror
    </div>

    <button type="submit" class="update-btn" dusk="purchase-submit">更新する</button>
  </form>
</div>
@endsection
