@extends('layouts.default')
@section('content')
    <form class="box" method="POST">
        <div class="box__heading">
            {{ $channel ? "Редактировать канал: ".$channel->name : "Добавить канал" }}
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Название</label>
                <div class="input-container__inner">
                    <input class="input" name="name" value="{{$channel ? $channel->name : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Описание</label>
                <div class="input-container__inner">
                    <textarea id="editor" class="input input--textarea" name="description">{{$channel ? $channel->description : ""}}</textarea>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Лого</label>
                <div class="input-container__inner">
                    <picture-uploader type="logo" :channelid="{{$channel ? $channel->id : "null"}}" name="logo_id" :data="{{$channel->logo ? $channel->logo : "null"}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">История названий</label>
                <div class="input-container__inner">
                    <names-history-editor :channelid="{{$channel ? $channel->id : "null"}}" :data="{{$channel ? $channel->names : "{}"}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <button class="button">Сохранить</button>
        </div>
        @csrf
    </form>
    @if ($channel)
    <form class="box" action="/channels/merge" method="POST">
        <div class="box__heading">
            Объединить канал
        </div>
        <div class="box__inner">
            <input value="{{$channel->id}}" type="hidden" name="original_id" />
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Выберите канал</label>
                <select class="select" name="merged_id">
                    @foreach ($all_channels as $channel)
                    <option value="{{$channel->id}}">{{$channel->name}}</option>
                    @endforeach
                </select>
                <span class="input-container__message"></span>
            </div>
            <button class="button">Объединить</button>
        </div>
        @csrf
    </form>
    @endif
@endsection
