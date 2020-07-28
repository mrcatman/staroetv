@extends('layouts.default')
@section('content')
    <form class="form box" method="POST" action="{{ route('login') }}">
        <div class="box__heading">
            Вход на сайт
        </div>
        <div class="box__inner">
            <div class="response"></div>
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
            <button class="button">Войти</button>
        </div>
        @csrf
    </form>
@endsection
