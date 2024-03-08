@extends('layouts.default')
@section('content')
    <form class="form box" action="/profile/password" method="POST">
        <div class="box__heading">
            Редактирование пароля
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="inputs-group">
                <div class="inputs-group__contents">
                    <div class="input-container">
                        <label class="input-container__label">Старый пароль<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input type="password" class="input" name="old_password" value=""/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Новый пароль<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input type="password" class="input" name="password" value=""/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Повторите пароль<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input type="password" class="input" name="password_confirmation" value=""/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
            </div>


            <button class="button">Сохранить</button>
        </div>
        @csrf
    </form>
@endsection
