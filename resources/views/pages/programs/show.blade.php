@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$program->name}}</div>
            <a class="button button--light" href="/programs/{{$program->id}}/edit">Редактировать</a>
        </div>
        @if ($program->description != "")
        <div class="inner-page__content">
            <div class="inner-page__text-block">
                {!! $program->description !!}
            </div>
        </div>
        @endif
        <div class="row">
            <div class="box">
                <div class="box__heading">
                    <div class="box__heading__inner">
                        Видео <span class="box__heading__count">{{count($records)}}</span>
                    </div>

                </div>
                <div class="box__inner">
                    <div class="records-list">
                        @foreach($records as $record)
                            @include('blocks/record', ['record' => $record])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection