<div class="header__container">
    <div class="header">
        <a href="{{route('index')}}" class="header__title">
            @include ('blocks.global.logo')
        </a>
        <div class="header__menu">
            <a class="header__menu__link sidebar__menu__link--with-icon" href="{{route('records.video.index')}}">
                Видеоархив
            </a>
            <a class="header__menu__link sidebar__menu__link--with-icon" href="{{route('records.radio.index')}}">
                Радиоархив
            </a>
            <a class="header__menu__link" href="{{route('articles.index')}}">Публикации</a>
            <a class="header__menu__link" href="{{route('forum.index')}}">Форум</a>
        </div>
        <div class="auth-panel @auth auth-panel--logged-in @endauth">
            @auth
                <a class="auth-panel__avatar" href="{{route('users.show-me')}}"
                   style="background-image:url({{auth()->user()->avatar ? auth()->user()->avatar->url : '/img/profnoava.png'}})"></a>
                <div class="auth-panel__buttons">
                    @php($pm = auth()->user()->unreadMessages())
                    <a href="{{route('pm.index')}}" class="auth-panel__button auth-panel__button--pm">
                        <span class="tooltip">Личные сообщения</span>
                        <i class="fa fa-envelope"></i>
                        <span style="display: none"
                              class="auth-panel__button__count @if($pm > 0) auth-panel__button__count--visible @endif">{{$pm}}</span>
                    </a>

                    <a class="auth-panel__button auth-panel__button--notifications">
                        <span class="tooltip">Уведомления</span>
                        <i class="fa fa-bell"></i>
                        <span style="display: none"
                              class="auth-panel__button__count">{{count(auth()->user()->unreadNotifications)}}</span>
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
                    <a class="button button--login" href="{{route('login')}}">Вход</a>
                    <a class="button button--register" href="{{route('register')}}">Регистрация</a>
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
<div class="header__second-menu">
    <div class="header__second-menu__inner">
        <a class="header__second-menu__link" href="{{route('teletext.index')}}">
            <i class="fa fa-list"></i>
            Телетекст
        </a>
        <a class="header__second-menu__link" href="{{route('records.video.commercials')}}">
            <i class="fa fa-star"></i>
            Рекламные ролики
        </a>
        <a class="header__second-menu__link" href="{{route('records.video.search')}}">
            <i class="fa fa-search"></i>Расширенный поиск записей
        </a>
        @if (auth()->user())
        <a class="header__second-menu__link header__second-menu__link--right"  onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            <i class="fa fa-sign-out-alt"></i>Выйти из аккаунта
        </a>
            @endif
    </div>

</div>
<div class="mobile-menu" style="display: none">
    <div class="mobile-menu__items">
        <a class="mobile-menu__item" href="{{route('records.video.index')}}">Видеоархив</a>
        <a class="mobile-menu__item" href="{{route('records.radio.index')}}">Радиоархив</a>
        <a class="mobile-menu__item" href="{{route('articles.index')}}">Публикации</a>
        <a class="mobile-menu__item" href="{{route('forum.index')}}">Форум</a>
        <div class="mobile-menu__delimiter"></div>
        <a class="mobile-menu__item mobile-menu__item--search">
            <i class="fa fa-search"></i>
            Поиск
        </a>
        @auth
            <a href="{{route('pm.index')}}" class="mobile-menu__item mobile-menu__item--pm">
                <i class="fa fa-envelope"></i>
                Личные сообщения
                <span style="display: none"
                      class="mobile-menu__item__count @if($pm > 0) mobile-menu__item__count--visible @endif">{{$pm}}</span>
            </a>
            <a class="mobile-menu__item mobile-menu__item--notifications">
                <i class="fa fa-bell"></i>
                Уведомления
                <span style="display: none"
                      class="mobile-menu__item__count">{{count(auth()->user()->unreadNotifications)}}</span>
            </a>
            <a class="mobile-menu__item"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
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

@php ($month = \Carbon\Carbon::now()->month)
@php ($day = \Carbon\Carbon::now()->day)
@if (($month == 12 && $day > 20) || ($month == 1 && $day < 20))
    @include('blocks.garland')
@endif

