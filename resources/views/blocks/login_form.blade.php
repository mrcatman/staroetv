<div id="login" data-title="Вход на сайт" style="display: none">
    <form class="form" method="POST" action="{{ route('login') }}">
        <div class="modal-window__form">
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
                <div class="form__bottom__left">
                    <button class="button button--light">Войти</button>
                    <div class="response response--light"></div>
                </div>
                <div class="form__bottom__right">
                    <a class="form__bottom__link" href="/forgot-password">Забыли пароль?</a>
                    <a class="form__bottom__link" href="/register">Регистрация</a>
                </div>
            </div>
            @csrf
        </div>
    </form>
</div>