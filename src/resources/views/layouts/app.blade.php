<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>mogitate</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header-left">
            <h1 class="header-title">
                <a href="{{ route('products.index') }}">
                    <img src="{{ asset('uploads/products/logo.svg') }}" alt="Logo">
                </a>
            </h1>
        </div>
        <div class="header-right">
            @auth
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    ログアウト
                </a>
                <a href="{{ route('mypage.index') }}">マイページ</a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="header-link">ログイン</a>
                <a href="{{ route('register') }}" class="header-link">会員登録</a>
            @endguest
        </div>
    </header>

    <main class="main-content">
        @yield('content')
    </main>

    @yield('js')
</body>
</html>
