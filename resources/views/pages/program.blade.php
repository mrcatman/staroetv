@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$program->name}}</div>
            <a class="button button--light" href="/programs/{{$program->id}}/edit">Редактировать</a>
        </div>
        <div class="inner-page__content">
            <div class="inner-page__text-block">
                {!! $program->description !!}
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