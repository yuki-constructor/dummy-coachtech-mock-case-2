<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ログイン（管理者）</title>
    <link rel="stylesheet" href="{{ asset('css/auth/admin/login.css') }}" />
</head>

<body>
    <header class="header">
        <div class="header-container">
            <div class="header-left">
                <img src="{{ asset('storage/photos/logo_images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo" />
            </div>
            <div class="header-center">

            </div>
            <div class="header-right">

            </div>
        </div>
    </header>

    <main>
        <div class="container-wrap">
            <div class="container">
            <h1 class="title">管理者ログイン</h1>
             {{-- ▼▼▼▼▼▼▼▼▼▼▼▼（メッセージ表示） --}}
             <div class="message">
                @if (session()->has('error'))
                    <p>{{ session()->get('error') }}</p>
                @endif
            </div>
            {{-- ▲▲▲▲▲▲▲▲▲▲▲▲ --}}
                <form class="form" method="POST" action="{{ route('admin.authenticate') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-group__label" for="email">メールアドレス</label>
                        <div>
                            {{-- エラーメッセージ --}}
                            @if ($errors->has('email'))
                                <div class="error-message">
                                    <ul>
                                        @foreach ($errors->get('email') as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <input class="form-group__input" type="text" id="email" name="email" />
                    </div>

                    <div class="form-group">
                        <label class="form-group__label" for="password">パスワード</label>
                        <div>
                            {{-- エラーメッセージ --}}
                            @if ($errors->has('password'))
                                <div class="error-message">
                                    <ul>
                                        @foreach ($errors->get('password') as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <input class="form-group__input" type="password" id="password" name="password" />
                    </div>
                    <button type="submit" class="form-group__submit-btn">管理者ログインする</button>
                </form>

            </div>
        </div>
    </main>
</body>

</html>
