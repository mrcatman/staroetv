@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$data['is_radio'] ? "Архив старых радиозаписей" : "Архив старых видеозаписей"}}</div>
            <div class="inner-page__header__right">
                @if (\App\Helpers\PermissionsHelper::allows('viadd'))
                    @if ($data['is_radio'])
                        <a class="button button--light" href="/radio-recordings/add">Добавить радиозапись</a>
                    @else
                        <a class="button button--light" href="/videos/add">Добавить видео</a>
                    @endif
                @endif
                @if (\App\Helpers\PermissionsHelper::allows('channelsown'))
                    @if ($data['is_radio'])
                    <a class="button button--light" href="/channels/add?is_radio=1">Добавить радиостанцию</a>
                    @else
                    <a class="button button--light" href="/channels/add?is_radio=0">Добавить канал</a>
                    @endif
                @endif
            </div>
        </div>
        <div class="inner-page__content">
            <div class="tabs" data-id="channels">
                <a class="tab tab--active" data-content="federal">Федеральные</a>
                <a class="tab" data-content="regional">Региональные</a>
                <a class="tab" data-content="abroad">Зарубежные</a>
                <a class="tab" data-content="other">Другие</a>
            </div>
            <div class="tab-content" data-id="channels" data-tab="federal">
                <div class="channels-list">
                    @foreach($federal as $channel)
                        <a href="/channels/{{$channel->url}}" class="channel-item">
                            <div class="channel-item__logo"   @if ($channel->logo) style="background-image:url({{$channel->logo->url}})"  @endif></div>
                            <span class="channel-item__name" >{{$channel->name}}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div style="display: none" class="tab-content" data-id="channels" data-tab="regional">
                <div class="cities-list">
                    <a class="cities-list__item cities-list__item--all">Все</a>
                    @foreach ($cities as $city => $count)
                        <a class="cities-list__item" data-city="{{$city}}">
                            <span class="cities-list__item__name">{{$city}}</span>
                            <span class="cities-list__item__count">{{$count}}</span>
                        </a>
                    @endforeach
                </div>
                <div class="channels-list">
                    @foreach($regional as $channel)
                        <a href="/channels/{{$channel->url}}" class="channel-item" data-city="{{$channel->city}}">
                            <div class="channel-item__logo"   @if ($channel->logo) style="background-image:url({{$channel->logo->url}})"  @endif></div>
                            <span class="channel-item__name" >{{$channel->name}}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div style="display: none" class="tab-content" data-id="channels" data-tab="abroad">
                <div class="channels-list">
                    @foreach($abroad as $channel)
                        <a href="/channels/{{$channel->url}}" class="channel-item">
                            <div class="channel-item__logo"   @if ($channel->logo) style="background-image:url({{$channel->logo->url}})"  @endif></div>
                            <span class="channel-item__name" >{{$channel->name}}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div style="display: none" class="tab-content" data-id="channels" data-tab="other">
                <div class="channels-list">
                    @foreach($other as $channel)
                        <a href="/channels/{{$channel->url}}" class="channel-item">
                            <div class="channel-item__logo"   @if ($channel->logo) style="background-image:url({{$channel->logo->url}})"  @endif></div>
                            <span class="channel-item__name" >{{$channel->name}}</span>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection