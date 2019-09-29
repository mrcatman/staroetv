@if (!request()->header('X-PJAX', false))
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Старый Телевизор</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header__inner">
                <a href="/" class="header__title">Старый Телевизор</a>
                <div class="auth-panel">
                    @auth
                        <a class="auth-panel__avatar" href="/users/{{auth()->user()->id}}" style="background-image:url({{auth()->user()->avatar ? auth()->user()->avatar->url : ''}})"></a>
                        <div class="auth-panel__texts">
                            <a href="{{auth()->user()->url}}" class="auth-panel__username">{{auth()->user()->username}}</a>
                            <a class="auth-panel__logout" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                Выход
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
    <div class="top-menu">
        <div class="container">
            <div class="top-menu__inner">
                <a class="top-menu__link" href="/videos">Видеоархив</a>
                <a class="top-menu__link" href="/news">Новости</a>
                <a class="top-menu__link" href="/blog">Блоги</a>
                <a class="top-menu__link" href="/articles">Статьи</a>
                <a class="top-menu__link" href="/forum">Форум</a>
            </div>
        </div>
    </div>

    <div id="app" class="content">
        @endif
        <div class="container" id="pjax-container">
            @yield('content')
        </div>
        @if (!request()->header('X-PJAX', false))
    </div>
</body>

<script type="text/javascript" rel="script" src="{{asset('js/app.js')}}"></script>
<script src="//cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
<script>
    if ($('#editor').length > 0){
        CKEDITOR.replace('editor');
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@yield('scripts')
</html>
@endif