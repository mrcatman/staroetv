@extends('layouts.default')
@section('page-title')
    Календарь
@endsection
@section('content')

    <div class="calendar-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="box">
                    <div class="box__breadcrumbs">
                        <div class="breadcrumbs">
                            <a class="breadcrumbs__item" href="{{typed_route('records.[RECORD].index', $is_radio)}}">Архив</a>
                            <a class="breadcrumbs__item breadcrumbs__item--current">Календарь</a>
                        </div>
                    </div>
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            Записи по годам
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="calendar-page__years">
                            @foreach ($years as $year)
                                <a href="{{typed_route('records.[RECORD].calendar.year', $is_radio, $year->year)}}" class="calendar-page__year">
                                    <span class="calendar-page__year__value">{{$year->year}}</span>
                                    <span class="calendar-page__year__count">{{$year->count_year}}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col col--sidebar">
                @include('blocks/generic_sidebar', ['hide_articles' => true, 'is_radio' => $is_radio])
            </div>

        </div>
@endsection
