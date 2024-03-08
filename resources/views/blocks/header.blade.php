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
            <a class="sidebar__menu__link" href="/articles">Публикации</a>
            <a class="sidebar__menu__link" href="/forum">Форум</a>
        </div>
        <div class="auth-panel @auth auth-panel--logged-in @endauth">
            @auth
                <a class="auth-panel__avatar" href="/index/8" style="background-image:url({{auth()->user()->avatar ? auth()->user()->avatar->url : '/img/profnoava.png'}})"></a>
                <div class="auth-panel__buttons">
                    <a class="auth-panel__button auth-panel__button--logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <span class="tooltip">Выйти из аккаунта</span>
                        <i class="fa fa-sign-out-alt"></i>
                    </a>
                    @php($pm = auth()->user()->unreadMessages())
                    <a href="/pm" class="auth-panel__button auth-panel__button--pm">
                        <span class="tooltip">Личные сообщения</span>
                        <i class="fa fa-envelope"></i>
                        <span style="display: none" class="auth-panel__button__count @if($pm > 0) auth-panel__button__count--visible @endif">{{$pm}}</span>
                    </a>

                    <a class="auth-panel__button auth-panel__button--notifications">
                        <span class="tooltip">Уведомления</span>
                        <i class="fa fa-bell"></i>
                        <span style="display: none" class="auth-panel__button__count">{{count(auth()->user()->unreadNotifications)}}</span>
                    </a>
                    <div class="notifications" style="display: none">
                        <div class="notifications__close">
                            <i class="fa fa-times"></i>
                        </div>
                        <div class="notifications__list"></div>
                    </div>
                    <a class="auth-panel__button auth-panel__button--search">
                        <span class="tooltip">Поиск</span>
                        <i class="fa fa-search"></i>
                    </a>
                   <a class="auth-panel__button auth-panel__button--menu">
                       <i class="fa fa-bars"></i>
                       <span class="auth-panel__button--menu__text">Меню</span>
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
                <a class="auth-panel__button auth-panel__button--search">
                    <span class="tooltip">Поиск</span>
                    <i class="fa fa-search"></i>
                </a>
                <a class="auth-panel__button auth-panel__button--menu">
                    <i class="fa fa-bars"></i>
                    <span class="auth-panel__button--menu__text">Меню</span>
                </a>
            @endguest
        </div>
    </div>
</div>
<div class="mobile-menu" style="display: none">
    <div class="mobile-menu__items">
        <a class="mobile-menu__item" href="/video">Видеоархив</a>
        <a class="mobile-menu__item" href="/radio">Радиоархив</a>
        <a class="mobile-menu__item" href="/articles">Публикации</a>
        <a class="mobile-menu__item" href="/forum">Форум</a>
        <div class="mobile-menu__delimiter"></div>
        <a class="mobile-menu__item mobile-menu__item--search">
            <i class="fa fa-search"></i>
            Поиск
        </a>
        @auth
            <a href="/pm" class="mobile-menu__item mobile-menu__item--pm">
                <i class="fa fa-envelope"></i>
                Личные сообщения
                <span style="display: none" class="mobile-menu__item__count @if($pm > 0) mobile-menu__item__count--visible @endif">{{$pm}}</span>
            </a>
            <a class="mobile-menu__item mobile-menu__item--notifications">
                <i class="fa fa-bell"></i>
                Уведомления
                <span style="display: none" class="mobile-menu__item__count">{{count(auth()->user()->unreadNotifications)}}</span>
            </a>
            <a class="mobile-menu__item" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fa fa-sign-out-alt"></i>
                Выйти из аккаунта
            </a>
            @endauth
        </div>
    </div>
<div class="site-search" style="display: none">
    <div class="container">
        <div class="site-search__inner">
            <a class="site-search__close">
                <i class="fa fa-times"></i>
            </a>
            <div class="site-search__top">
                <input class="input site-search__input" placeholder="Поиск по сайту...">
            </div>
            <div class="site-search__results">

            </div>
        </div>
    </div>
</div>

<!--
<div class="garland">
    @for($i = 1; $i < 50; $i++)
        <li></li>
    @endfor
</div>
-->

