@extends('layouts.default')
@section('content')
    <div class="private-messages">
        <div class="private-messages__heading">
            <span class="private-messages__heading__text">{{$message->title ? $message->title : "Без темы"}}   @if ($user) (<a href="/index/8-{{$user->id}}">{{$user->username}}</a>) @endif</span>
            <div class="private-messages__heading__right">
                <a class="button button--flat" href="/pm">Список</a>
                <a class="button button--flat" href="/pm/send">Написать новое</a>
            </div>

        </div>
        <div class="private-message-page">
            <div class="private-message-page__text">
                {!! $message->text !!}
            </div>
            @if ($user)
            <form action="/pm/send" class="form box private-message-page__form">
                <div class="private-message-page__form__title">Написать ответ</div>
                <div class="private-message-page__form__inner">
                    <input type="hidden" name="to_id" value="{{$user->id}}">
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
                </div>
            </form>
            @endif
        </div>
    </div>
@endsection
@section ('scripts')
    <script>
        window.pm.updateCount();
        var bb = new bbCodes();
        bb.init('message');
    </script>
@endsection
