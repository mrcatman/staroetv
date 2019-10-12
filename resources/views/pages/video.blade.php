@extends('layouts.default')
@section('content')
    <div class="inner-page video-page">
        <div class="row row--stretch">
            <div class="col col--3">
                <div class="inner-page__header">
                    <div class="inner-page__header__title">{{$video->title}}</div>
                </div>
                <div class="inner-page__content">
                    <div class="row">
                        <div class="video-page__player-container">
                            {!! $video->embed_code !!}
                        </div>
                    </div>
                    <div class="video-page__bottom">
                        <a href="/channels/{{$video->channel->id}}" class="video-page__channel">
                            <div class="video-page__channel__logo" style="background-image: url({{$video->getChannelLogo()}})"></div>
                            <div class="video-page__channel__name">
                                {{$video->getChannelName()}}
                            </div>
                        </a>
                        <div class="inner-page__icon-blocks-container">
                            <a @if ($video->user) href="{{$video->user->url}}" @endif class="inner-page__icon-block">
                                <i class="fa fa-user"></i>
                                <span class="inner-page__icon-block__text">{{$video->user ? $video->user->username : $video->author_username}}</span>
                            </a>
                        </div>
                    </div>
                    <div class="inner-page__text">
                        {{ $video->description }}
                    </div>
                </div>
                <div class="box form video-page__comments">
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            Комментарии <span class="box__heading__count">{{\App\Comment::where(['material_type' => 10, 'material_id' => $video->ucoz_id])->count()}}</span>
                        </div>
                    </div>
                    <div class="box__inner">
                        @include('blocks/comments', ['ajax' => false, 'page' => 1, 'conditions' => ['material_type' => 10, 'material_id' => $video->ucoz_id]])
                    </div>
                </div>
            </div>
            <div class="col col--2 video-page__related-container">
                @if ($related_program)
                <div class="box">
                    <div class="box__heading box__heading--small">
                        <div class="box__heading__inner">
                            Другие выпуски программы <span class="box__heading__count">{{$video->program->name}}</span>
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="video-page__related">
                        @foreach ($related_program as $video)
                            @include('blocks/video', ['video' => $video])
                        @endforeach
                        </div>
                    </div>
                </div>
                @endif
                @if ($related_channel)
                <div class="box">
                    <div class="box__heading box__heading--small">
                        <div class="box__heading__inner">
                            Видео с канала <span class="box__heading__count">{{$video->channel->name}}</span>
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="video-page__related">
                            @foreach ($related_channel as $video)
                                @include('blocks/video', ['video' => $video])
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>

@endsection