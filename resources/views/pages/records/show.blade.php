@extends('layouts.default')
@section('page-title')

@endsection
@section('content')
    <div class="inner-page record-page">
        <div class="breadcrumbs">
            @if ($record->channel)
            <a class="breadcrumbs__item" href="{{$record->channel->is_radio ? "/radio" : "/video"}}">Архив</a>
            <a class="breadcrumbs__item" href="{{$record->channel->full_url}}">{{$record->channel->name}}</a>
            @endif
            @if ($record->program)
            <a class="breadcrumbs__item" href="{{$record->program->full_url}}">{{$record->program->name}}</a>
            @endif
            <a class="breadcrumbs__item breadcrumbs__item--current">{{$record->title}}</a>
        </div>
        <div class="row row--stretch">
            <div class="col col--3">
                <div class="inner-page__header">
                    <div class="inner-page__header__title">{{$record->title}}</div>
                </div>
                <div class="inner-page__content">
                    @if ($record->is_radio)
                        {!! $record->embed_code !!}
                    @else
                    <div class="row">
                        <div class="record-page__player-container">
                            {!! $record->embed_code !!}
                        </div>
                    </div>
                    @endif
                    <div class="record-page__bottom">
                        @if ($record->channel)
                        <a href="/channels/{{$record->channel->id}}" class="record-page__channel">
                            <div class="record-page__channel__logo" style="background-image: url({{$record->getChannelLogo()}})"></div>
                            <div class="record-page__channel__name">
                                {{$record->getChannelName()}}
                            </div>
                        </a>
                        @endif
                        <div class="inner-page__icon-blocks-container">
                            <a @if ($record->user) href="{{$record->user->url}}" @endif class="inner-page__icon-block">
                                <i class="fa fa-user"></i>
                                <span class="inner-page__icon-block__text">{{$record->user ? $record->user->username : $record->author_username}}</span>
                            </a>
                        </div>
                    </div>
                    <div class="inner-page__text">
                        {{ $record->description }}
                    </div>
                </div>
                <div class="box form record-page__comments">
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            Комментарии <span class="box__heading__count">{{\App\Comment::where(['material_type' => 10, 'material_id' => $record->ucoz_id])->count()}}</span>
                        </div>
                    </div>
                    <div class="box__inner">
                        @include('blocks/comments', ['ajax' => false, 'page' => 1, 'conditions' => ['material_type' => 10, 'material_id' => $record->ucoz_id]])
                    </div>
                </div>
            </div>
            <div class="col col--2 record-page__related-container">
                @if ($related_program)
                <div class="box">
                    <div class="box__heading box__heading--small">
                        <div class="box__heading__inner">
                            Другие выпуски программы <span class="box__heading__count">{{$record->program->name}}</span>
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="record-page__related">
                        @foreach ($related_program as $record)
                            @include('blocks/record', ['record' => $record])
                        @endforeach
                        </div>
                    </div>
                </div>
                @endif
                @if ($related_channel)
                <div class="box">
                    <div class="box__heading box__heading--small">
                        <div class="box__heading__inner">
                            Видео с канала <span class="box__heading__count">{{$record->channel->name}}</span>
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="record-page__related">
                            @foreach ($related_channel as $record)
                                @include('blocks/record', ['record' => $record])
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>

@endsection
