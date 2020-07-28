@extends('layouts.default')
@section('content')
    <form class="form box" method="POST" >
        <div class="box__heading">
            Восстановление аккаунта
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Ваша почта</label>
                <div class="input-container__inner">
                    <input class="input" name="email" value=""/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <button class="button">Отправить ссылку для восстановления</button>
        </div>
        @csrf
    </form>
@endsection
