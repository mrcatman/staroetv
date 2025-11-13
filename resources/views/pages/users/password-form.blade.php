@extends('layouts.default')
@section('page-title')
    Изменение пароля
@endsection
@section('content')
    <form class="form box" action="/profile/password" method="POST">
        <div class="box__heading">
            <div class="box__heading__inner">
                Изменение пароля
            </div>
            <div class="box__heading__right">
                <a class="button button--light" href="/index/8-{{auth()->user()->id}}">Назад</a>
            </div>

        </div>
        <div class="box__inner">
            @csrf
            <div class="response"></div>
            <div class="form__content">
                <div class="input-container">
                    <label class="input-container__label">Старый пароль<span class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <input type="password" class="input" name="old_password" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Новый пароль<span
                            class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <input type="password" class="input" name="password" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Повторите пароль<span
                            class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <input type="password" class="input" name="password_confirmation" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>

                <button class="button">Сохранить</button>

            </div>

        </div>

    </form>
@endsection
