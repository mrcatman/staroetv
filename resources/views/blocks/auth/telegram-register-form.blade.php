<div>
    <form class="form modal-window__form telegram-create-new-profile-form" method="POST" action="{{route('profile.telegram.register')}}">
        @csrf
        <input type="hidden" name="telegram_data" value="{{json_encode($telegram_data)}}"/>
        <div class="form__content">
            <div class="input-container">
                <label class="input-container__label">Придумайте логин</label>
                <div class="input-container__inner">
                    <input class="input" name="username" value="{{$username}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="form__bottom">
                <div class="form__bottom__left buttons-row">
                    <button class="button">Зарегистрироваться</button>
                </div>
                <div class="form__bottom__right">
                    <a class="form__bottom__link telegram-connect-existing-profile-link">Привязать Телеграм к уже имеющемуся профилю</a>
                </div>
            </div>
            <div class="response response--light"></div>
        </div>

    </form>
    <form class="form modal-window__form telegram-connect-existing-profile-form" style="display: none" method="POST" action="{{route('login')}}">
        <input type="hidden" name="telegram_data" value="{{json_encode($telegram_data)}}"/>
        <div class="form__content">
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
                </div>
                <div class="form__bottom__right">
                    <a class="form__bottom__link telegram-create-new-profile-link">У меня ещё нет аккаунта на сайте</a>
                </div>
            </div>
        </div>
        <div class="response response--light"></div>

    </form>


</div>
