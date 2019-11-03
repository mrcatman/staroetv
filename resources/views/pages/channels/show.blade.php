@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$channel->name}}</div>
            <a class="button button--light" href="/channels/{{$channel->id}}/edit">Редактировать</a>
        </div>
        <div class="inner-page__content">
            @if ($channel->description != "")
            <div class="inner-page__text-block">
                {!! $channel->description !!}
            </div>
            @endif
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
                    <div class="box__heading__inner">
                        {{$channel->is_radio ? "Радиозаписи" : "Видеозаписи"}} <span class="box__heading__count">{{$records_count}}</span>
                    </div>
                </div>
                <div class="box__inner">
                    <div class="records-list">
                        @foreach($records as $record)
                            @include('blocks/record', ['record' => $record])
                        @endforeach
                    </div>
                    <div class="records-list__pager-container">
                        {{$records->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection