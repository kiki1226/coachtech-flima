<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>COACHTECH FLEMA</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
    <img src="{{ asset('uploads/products/logo.svg') }}" alt="Logo">
            @auth
            @if (Route::has('search'))
            <form action="{{ route('search') }}" method="GET" class="search-form">
                <input type="text" name="keyword" placeholder="なにをお探しですか？">
                <input type="hidden" name="bulk_like" value="1"> {{-- 🔽 これを追加 --}}
                <button type="submit" class="nav-button-search">検索</button>
            </form>

            @endif
        @endauth
        @auth
            <div class="welcome">ようこそ、{{ Auth::user()->username ?? Auth::user()->email }} さん</div>
        @endauth
        <nav class="header-nav">
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="header-nav-button">ログアウト</button>
            </form>
            <a href="{{ route('mypage.index') }}" class="header-nav-button">マイページ</a>

            @auth
                <a href="{{ route('products.create') }}" class="nav-button-syutten">出品</a>
            @endauth
        </nav>
    </header>

    <main class="main-content">
        @yield('content')
    </main>
    @yield('js')
</body>

</html>
