<div class="sidebar__container">
    <div class="sidebar">
        <a href="/" class="sidebar__title">
            @include ('blocks/logo')
            <!--
            <img class="sidebar__logo sidebar__logo--shadow" src="/pictures/logo.png"/>
            -->
        </a>
        <div class="sidebar__menu">
            <a class="sidebar__menu__link sidebar__menu__link--with-icon" href="/video">
                <!--<img class="sidebar__menu__link__icon" src="/img/new/vhs.png"/> -->
                Видеоархив
            </a>
            <a class="sidebar__menu__link sidebar__menu__link--with-icon" href="/radio">
                <!--<img class="sidebar__menu__link__icon" src="/img/new/audio.png"/>-->
                Радиоархив
            </a>
            <a class="sidebar__menu__link" href="/articles">Тексты</a>
            <a class="sidebar__menu__link" href="/forum">Форум</a>
        </div>
        <div class="auth-panel @auth auth-panel--logged-in @endauth">
            @auth
                <a class="auth-panel__avatar" href="/index/8" style="background-image:url({{auth()->user()->avatar ? auth()->user()->avatar->url : '/img/profnoava.png'}})"></a>
                <div class="auth-panel__buttons">
                    <!--
                    <a href="{{auth()->user()->url}}" class="auth-panel__username">{{auth()->user()->username}}</a>
                    -->
                    @include('blocks.pm')

                    <a class="auth-panel__button auth-panel__button--notifications">
                        <i class="fa fa-bell"></i>
                        <span class="auth-panel__button__count">{{count(auth()->user()->unreadNotifications)}}</span>
                        <div class="notifications" style="display: none">
                            <div class="notifications__list"></div>
                        </div>
                    </a>
                    <a class="auth-panel__button auth-panel__button--logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <i class="fa fa-sign-out-alt"></i>
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            @endauth
            @guest
                <div class="buttons-row">
                    <a class="button button--login" href="/login">Вход</a>
                    <a class="button button--register" href="/register">Регистрация</a>
                </div>

            @endguest
        </div>
    </div>
</div>
