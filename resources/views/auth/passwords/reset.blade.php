@extends('layouts.default')

@section('content')
    <form class="form box" action="{{route('profile.reset-password')}}" method="POST" >
        <div class="box__heading">
            <div class="box__heading__inner">
                Новый пароль
            </div>
        </div>
        <div class="box__inner">
            <div class="form__content">
                <div class="response"></div>
                <input type="hidden" name="token" value="{{ $token }}">
                <input name="email" type="hidden" value="{{ $email }}">
                <div class="input-container">
                    <label class="input-container__label">Новый пароль</label>
                    <div class="input-container__inner">
                        <input class="input" name="password" type="password" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Повторите пароль</label>
                    <div class="input-container__inner">
                        <input class="input" name="password_confirmation" type="password" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <button class="button">Изменить пароль</button>
            </div>

        </div>
        @csrf
    </form>
@endsection
