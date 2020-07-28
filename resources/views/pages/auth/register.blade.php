@extends('layouts.default')
@section('content')
    <form class="form box" method="POST" action="{{ route('register') }}">
        <div class="box__heading">
            Регистрация
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Ник<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <input class="input" name="username" required value=""/>
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
                <label class="input-container__label">Повторите пароль<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <input class="input" type="password" required name="password_confirmation" value=""/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="horisontal-delimiter"></div>
            <div class="input-container">
                <label class="input-container__label">Ваше имя</label>
                <div class="input-container__inner">
                    <input class="input" name="name" value=""/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="horisontal-delimiter"></div>
            <div class="input-container">
                <label class="input-container__label">Код безопасности<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <img class="captcha" src="{{captcha_src()}}"/>
                    <input class="input" name="captcha" required value=""/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <button class="button">Регистрация</button>
        </div>
        @csrf
    </form>
@endsection
