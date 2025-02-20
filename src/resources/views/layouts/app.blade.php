<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'デフォルトタイトル')</title>
    <!-- 共通のCSS -->
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <!-- ページごとのCSS -->
    @stack('styles')
</head>

<body>
    <!-- 共通のヘッダー -->
    <header class="header">
        <div class="header-container">
            <div class="header-left">
                <img src="{{ asset('storage/photos/logo_images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo" />
            </div>
            <div class="header-center"></div>
            <div class="header-right">
                <nav class="nav">
                    <ul class="nav__ul">
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                <!-- @csrf -->
                                <button type="submit" class="nav__attendance">勤怠</button>
                            </form>
                        </li>
                        <li>
                            <form action="" method="GET">
                                <!-- @csrf -->
                                <button type="submit" class="nav__attendance-list">
                                    勤怠一覧
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="" method="GET">
                                <!-- @csrf -->
                                <button type="submit" class="nav__application">申請</button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="nav__logout">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <!-- ページごとのコンテンツ -->
        @yield('content')
    </main>
</body>

</html>
