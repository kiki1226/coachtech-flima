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
    <span> ＞ プロフィール確認</span>
</div>

{{-- プロフィール確認 --}}
<div class="profile-container">
    
    <h2>プロフィールの確認</h2>
    
    <div class="profile-form">
    <form method="GET" action="{{ route('profile.edit') }}">
            @csrf

            <div class="image-upload">
                <div class="profile-icon" id="preview">
                    <img src="{{ $user->avatar ? asset($user->avatar) : asset('uploads/avatars/no-image.png') }}" alt="" readonly>
                </div>
                @if (session('success'))
                    <div class="success">
                        {{ session('success') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label>ユーザー名</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" readonly>
            </div>

            <div class="form-group">
                <label>郵便番号</label>
                <input type="text" name="zipcode" value="{{ $user->zipcode }}" readonly>
            </div>

            <div class="form-group">
                <label>住所</label>
                <input type="text" name="address" value="{{ $user->address }}" readonly>
            </div>

            <div class="form-group">
                <label>建物名</label>
                <input type="text" name="building" value="{{ $user->building }}" readonly>
            </div>

            <div class="form-button">
                 <button type="submit" class="btn btn-outline">プロフィールを編集</button>
            </div>

        </form>
    </div>
</div>
@endsection
