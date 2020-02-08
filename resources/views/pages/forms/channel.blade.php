@extends('layouts.default')
@section('content')
    <form class="form box" method="POST">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="{{$is_radio ? "/radio" : "/video"}}">Архив</a>
            @if ($channel)
                <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
                <a class="breadcrumbs__item breadcrumbs__item--current">Редактировать</a>
            @else
                <a class="breadcrumbs__item breadcrumbs__item--current">{{$is_radio ? "Новая радиостанция" : "Новый канал"}}</a>
            @endif
        </div>
        <div class="box__heading">
            {{ $is_radio ? ($channel ? "Редактировать радиостанцию: ".$channel->name : "Добавить радиостанцию") : ($channel ? "Редактировать канал: ".$channel->name : "Добавить канал") }}
        </div>
        <input type="hidden" name="is_radio" value="{{$is_radio ? 1 : 0}}"/>
        <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Название<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <input class="input" name="name" id="channel_name" value="{{$channel ? $channel->name : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Короткий URL</label>
                <div class="input-container__inner">
                    <input class="input" name="url" id="channel_url" value="{{$channel ? $channel->url : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Описание</label>
                <div class="input-container__inner">
                    <textarea class="input input--textarea" name="description">{{$channel ? $channel->description : ""}}</textarea>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Логотип</label>
                <div class="input-container__inner">
                    <picture-uploader type="logo" :channelid="{{$channel ? $channel->id : "null"}}" name="logo_id" :data="{{$channel && $channel->logo ? $channel->logo : "null"}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label class="input-container input-container--checkbox">
                        <input type="checkbox" name="is_federal" {{$channel && $channel->is_federal ? "checked" : ""}}>
                        <div class="input-container--checkbox__element"></div>
                        <div class="input-container__label">{{$is_radio ? "Федеральная?" : "Федеральный?"}}</div>
                    </label>
                </div>
                <div class="col">
                    <label class="input-container input-container--checkbox input-container--checkbox--toggle">
                        <input type="checkbox" name="is_regional" {{$channel && $channel->is_regional ? "checked" : ""}}>
                        <div class="input-container--checkbox__element"></div>
                        <div class="input-container__label">{{$is_radio ? "Региональная?" : "Региональный?"}}</div>
                        <input value="{{$channel ? $channel->city : ""}}" {{$channel && $channel->is_regional ? "" : "disabled"}} class="input input--inline-label" placeholder="Город или регион" name="city"/>
                    </label>
                </div>
                <div class="col">
                    <label class="input-container input-container--checkbox input-container--checkbox--toggle">
                        <input type="checkbox" name="is_abroad" {{$channel && $channel->is_abroad ? "checked" : ""}}>
                        <div class="input-container--checkbox__element"></div>
                        <div class="input-container__label">{{$is_radio ? "Зарубежная?" : "Зарубежный?"}}</div>
                        <input value="{{$channel ? $channel->country : ""}}" {{$channel && $channel->is_regional ? "" : "disabled"}} class="input input--inline-label" placeholder="Страна" name="country"/>
                    </label>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">История названий</label>
                <div class="input-container__inner">
                    <names-history-editor :channelid="{{$channel ? $channel->id : "null"}}" :data="{{$channel ? $channel->names : "[]"}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <button class="button">Сохранить</button>
        </div>
        @csrf
    </form>
    @if ($channel)
    <form class="form box" action="/channels/merge" method="POST">
        <div class="box__heading">
            Объединить канал
        </div>
        <div class="box__inner">
            <input value="{{$channel->id}}" type="hidden" name="original_id" />
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Выберите канал</label>
                <select class="select-classic" name="merged_id">
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
