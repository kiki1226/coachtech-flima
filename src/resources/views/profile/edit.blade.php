@extends('layouts.authenticated')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')

<script>
function previewImage(event) {
    const input = event.target;
    const previewImg = document.querySelector('#preview img');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
{{-- パン屑リスト --}}
<div class="breadcrumb">
    <a href="{{ route('products.index') }}">商品一覧</a>
    <span> ＞ <a href="{{ route('mypage.index') }}">マイページ</a></span>
    <span> ＞ <a href="{{ route('profile.check') }}">プロフィール確認</a></span>
    <span> ＞プロフィール編集</span>
</div>

{{-- プロフィール編集 --}}

<div class="profile-container">
    <h2>プロフィールの編集</h2>

    <div class="profile-form">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="image-upload">
                <div class="profile-icon" id="preview">
                    <img src="{{ $user->avatar ? asset($user->avatar) : asset('uploads/avatars/no-image.png') }}" alt="" readonly>
                </div>
                <label class="upload-label" for="avatarInput">画像を選択する</label>
                <input type="file" id="avatarInput" name="avatar" accept="image/*" onchange="previewImage(event)">
            </div>

            <div class="form-group">
                <label>ユーザー名</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}">
            </div>

            <div class="form-group">
                <label>郵便番号</label>
                <input type="text" name="zipcode" value="{{ old('zipcode', $user->zipcode) }}">
            </div>

            <div class="form-group">
                <label>住所</label>
                <input type="text" name="address" value="{{ old('address', $user->address) }}">
            </div>

            <div class="form-group">
                <label>建物名</label>
                <input type="text" name="building" value="{{ old('building', $user->building) }}">
            </div>

            <div class="form-button">
                <button type="submit">更新する</button>
            </div>
        </form>
    </div>
</div>
@endsection
