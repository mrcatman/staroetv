<div class="header">
    <div class="container">
        <div class="header__inner">
            <a href="/" class="header__title">
                <img class="header__logo" src="/pictures/logo.png"/>
            </a>
            <div class="auth-panel">
                @auth
                    <a class="auth-panel__avatar" href="/users/{{auth()->user()->id}}" style="background-image:url({{auth()->user()->avatar ? auth()->user()->avatar->url : ''}})"></a>
                    <div class="auth-panel__texts">
                        <a href="{{auth()->user()->url}}" class="auth-panel__username">{{auth()->user()->username}}</a>
                        <a class="auth-panel__logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            Выход
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                @endauth
                @guest
                    <a class="button button--login" href="/login">Вход</a>
                    <a class="button button--register" href="/register">Регистрация</a>
                @endguest
            </div>
        </div>
    </div>
</div>
<div class="top-menu">
    <div class="container">
        <div class="top-menu__inner">
            <a class="top-menu__link" href="/video">Видеоархив</a>
            <a class="top-menu__link" href="/radio">Радиоархив</a>
            <a class="top-menu__link" href="/news">Новости</a>
            <a class="top-menu__link" href="/blog">Блоги</a>
            <a class="top-menu__link" href="/articles">Статьи</a>
            <a class="top-menu__link" href="/forum">Форум</a>
        </div>
    </div>
</div>