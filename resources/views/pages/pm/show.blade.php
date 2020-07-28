@extends('layouts.default')
@section('content')
    <div class="private-messages private-message-page box">
        <div class="box__heading">
           <div class="private-messages__title">
               {{$message->title ? $message->title : "Без темы"}}
           </div>
            <div class="box__heading__right box__heading__right--full-width">
                <a class="button" href="/pm">Список</a>
                <a class="button" href="/pm/send">Написать новое</a>
            </div>

        </div>
        <div class="box__inner">
            <div class="private-message-page__info">
                @if ($user) 
                <a href="/index/8-{{$user->id}}" class="private-message-page__info__item">
                    <i class="fa fa-user"></i>
                    {{$user->username}}
                </a>
                @endif
                <span class="private-message-page__info__item">
                    <i class="fa fa-clock"></i>
                    {{$message->created_at}}
                </span>
            </div>
            <div class="private-message-page__text">
                {!! $message->text !!}
            </div>
        </div>
    </div>
    @if ($user && !$message->is_out)
        <div class="box">
            <div class="box__heading">Написать ответ</div>
            <form action="/pm/send" class="form box__inner private-message-page__form">
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
        </div>
    </div>
    @endif
@endsection
@section ('scripts')
    <script>
        window.pm.updateCount();
    </script>
@endsection
