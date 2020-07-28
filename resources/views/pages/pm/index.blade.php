@extends('layouts.default')
@section('content')
    <div class="private-messages box">
        <div class="box__heading">
            Личные сообщения
            <div class="box__heading__right box__heading__right--full-width">
                <a class="button" href="/pm/send">Написать новое</a>
            </div>
        </div>
        <div class="box__inner">
            <div class="private-messages__list">
                <div class="tabs">
                    <a class="tab @if (!request()->has('type')) tab--active @endif " href="/pm">Входящие</a>
                    <a class="tab @if (request()->input('type') == "out") tab--active @endif " href="/pm?type=out">Исходящие</a>
                    <a class="tab @if (request()->input('type') == "all") tab--active @endif " href="/pm?type=all">Все</a>
                </div>
                @if (count($messages) === 0)
                    <div class="private-messages__empty">Нет сообщений</div>
                @endif
                @foreach ($messages as $message)
                   <div class="private-message @if ($message->is_unread) private-message--unread @endif" data-id="{{$message->id}}">
                        <a href="/pm/{{$message->id}}" class="private-message__title">{{$message->title ? $message->title : "Без темы"}}</a>
                        @if ($message->user) (<a href="/index/8-{{$message->user->id}}" class="private-message__user">{{$message->user->username}}</a>)
                        @endif
                        <div class="private-message__right">
                            <span class="private-message__time">{{$message->created_at}}</span>
                            <form data-confirm="1" data-confirm-text="Вы уверены, что хотите удалить это сообщение?" class="form" action="/pm/delete">
                                @csrf
                                <input type="hidden" name="message_id" value="{{$message->id}}"/>
                                <button class="private-message__button">
                                    <span class="tooltip">Удалить сообщение</span>
                                    <i class="fa fa-times"></i>
                                </button>
                            </form>
                            @if ($message->is_group && $can_mass_send)
                                <form data-confirm="1" data-confirm-text="Вы уверены, что хотите удалить это сообщение у всех пользователей?" class="form" action="/pm/cancel">
                                    @csrf
                                    <input type="hidden" name="message_id" value="{{$message->id}}"/>
                                    <button class="private-message__button">
                                        <span class="tooltip">Отменить групповую рассылку</span>
                                        <i class="fa fa-backspace"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
