@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">Все видео</div>
            <a class="button button--light" href="/videos/add">Добавить новое</a>
        </div>
        <div class="inner-page__content">
            <div class="channels-list">
            @foreach($channels as $channel)
                <a href="/channels/{{$channel->id}}" class="channel-item">
                    <div class="channel-item__logo"   @if ($channel->logo) style="background-image:url({{$channel->logo->url}})"  @endif></div>
                    <span class="channel-item__name" >{{$channel->name}}</span>
                </a>
            @endforeach
            </div>
        </div>
    </div>
@endsection