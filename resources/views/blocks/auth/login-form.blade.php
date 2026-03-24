@php($modal = isset($modal) ? $modal : false)
<form data-disable-redirects="1" data-callback="loginCallback" class="form @if ($modal) modal-window__form @endif" method="POST"
      action="{{ route('login') }}">
    <div class="form__content">
        @csrf
        @if (!$modal)
            <div class="response"></div>
        @endif
        <input type="hidden" name="telegram_data" value=""/>

        <div class="input-container">
            <label class="input-container__label">Логин или почта</label>
            <div class="input-container__inner">
                <input class="input" name="login" value=""/>
                <span class="input-container__message"></span>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Пароль</label>
            <div class="input-container__inner">
                <input class="input" type="password" name="password" value=""/>
                <span class="input-container__message"></span>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label class="input-container input-container--checkbox">
                    <input type="checkbox" name="remember" checked/>
                    <div class="input-container--checkbox__element"></div>
                    <div class="input-container__label">Запомнить меня</div>
                </label>
            </div>
        </div>
        <div class="form__bottom">
            <div class="form__bottom__left buttons-row">
                <button class="button">Войти</button>
                <button class="button button--telegram">
                    <i class="fab fa-telegram"></i>
                    Вход через Телеграм
                </button>
            </div>
            <div class="form__bottom__right">
                <a class="form__bottom__link" href="{{route('profile.forgot-password')}}">Забыли пароль?</a>
                <a class="form__bottom__link" href="{{route('register')}}">Регистрация</a>
            </div>
        </div>

        @if ($modal)
            <div class="response response--light"></div>
        @endif
    </div>
</form>
