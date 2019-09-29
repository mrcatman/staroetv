@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$channel->name}}</div>
            <a class="button button--light" href="/channels/{{$channel->id}}/edit">Редактировать</a>
        </div>
        <div class="inner-page__content">
            <div class="inner-page__text-block">
                {!! $channel->description !!}
            </div>
            <div class="programs-list">
                @foreach($programs as $program)
                    <a href="/programs/{{$program->id}}" class="program">
                        <span class="program__cover" style="background-image: url({{$program->coverPicture ? $program->coverPicture->url : ''}})"></span>
                        <span class="program__name">{{$program->name}}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="row">
            <div class="box">
                <div class="box__heading">
                    Видео <span class="box__heading__count">{{count($videos)}}</span>
                </div>
                <div class="box__inner">
                    <div class="videos-list">
                        @foreach($videos as $video)
                            @include('blocks/video', ['video' => $video])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection