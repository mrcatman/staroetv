@extends('layouts.default')
@section('content')
    <div class="private-messages">
        <div class="private-messages__heading">
            <span class="private-messages__heading__text">Личные сообщения</span>
            <a class="button button--flat" href="/pm/send">Написать новое</a>
        </div>
        <div class="private-messages__inner">
            <div class="private-messages__list">
                @foreach ($messages as $message)
                    <div class="private-message" data-id="{{$message->id}}">
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