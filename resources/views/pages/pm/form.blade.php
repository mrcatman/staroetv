@extends('layouts.default')
@section('content')
    <div class="private-messages">
        <div class="private-messages__heading">
            <span class="private-messages__heading__text">Написать новое сообщение</span>
            <a class="button button--flat" href="/pm">Назад</a>
        </div>
        <div class="private-messages__inner">
            <form method="POST" class="form">
                <div class="input-container">
                    <label class="input-container__label">Пользователь<span class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <select name="to_id" id="users_autocomplete">
                            @if ($user)
                                <option value="{{$user->id}}">{{$user->username}}</option>
                            @endif
                        </select>
                        <span class="input-container__message"></span>
                    </div>
                </div>

                <div class="input-container">
                    <label class="input-container__label">Заголовок</label>
                    <div class="input-container__inner">
                        <input class="input" name="title" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Текст<span class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        @include('blocks/bb_editor', ['name' => 'text'])
                        <span class="input-container__message"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section ('scripts')
    <script>
        var bb = new bbCodes();
        bb.init('message');
    </script>
@endsection