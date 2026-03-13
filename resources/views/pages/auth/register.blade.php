@extends('layouts.default')
@section('content')
    <form class="form  form--with-captcha box" method="POST" action="{{ route('register') }}">
        <div class="box__heading">
            <div class="box__heading__inner">
                Регистрация
            </div>

        </div>
        <div class="box__inner">
            <div class="form__content">
                <div class="response"></div>
                <div class="input-container">
                    <label class="input-container__label">Ник<span class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <input maxlength="32" class="input" name="username" required value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Почта<span class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <input type="email" class="input" name="email" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="horisontal-delimiter"></div>
                <div class="input-container">
                    <label class="input-container__label">Пароль<span class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <input class="input" type="password" required name="password" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Повторите пароль<span
                            class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <input class="input" type="password" required name="password_confirmation" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="horisontal-delimiter"></div>
                <div class="input-container">
                    <label class="input-container__label">Ваше имя</label>
                    <div class="input-container__inner">
                        <input maxlength="64" class="input" name="name" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>

                <label class="input-container input-container--checkbox">
                    <input type="checkbox" name="rules"/>
                    <div class="input-container--checkbox__element"></div>
                    <div class="input-container__label">
                        Я ознакомился с <a target="_blank" href="/index/0-133">правилами сайта</a>
                    </div>
                </label>

                <div class="buttons-row">
                    <button class="button">Регистрация</button>

                    <button class="button button--telegram" data-action="register">
                        <i class="fab fa-telegram"></i>
                        Регистрация через Телеграм
                    </button>
                </div>
            </div>
        </div>
        @csrf
    </form>
@endsection
