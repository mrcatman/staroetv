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
                    <div class="private-message" >
                        <a href="/pm/{{$message->id}}" class="private-message__title">{{$message->title ? $message->title : "Без темы"}}</a>
                        @if ($message->user) (<a href="/index/8-{{$message->user->id}}" class="private-message__user">{{$message->user->username}}</a>)
                        @endif
                        <span class="private-message__time">{{$message->created_at}}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection