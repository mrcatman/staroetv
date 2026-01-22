@extends('layouts.default')
@section('page-title')
    {{$year}} год - Календарь
@endsection
@section('content')

    <div class="calendar-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="box">
                    <div class="box__breadcrumbs">
                        <div class="breadcrumbs">
                            <a class="breadcrumbs__item" href="{{typed_route('records.[RECORD].index', $is_radio)}}">Архив</a>
                            <a class="breadcrumbs__item" href="{{typed_route('records.[RECORD].calendar.index', $is_radio)}}">Календарь</a>
                            <a class="breadcrumbs__item breadcrumbs__item--current">{{$year}}</a>
                        </div>
                    </div>
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            Все записи за {{$year}} год
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="calendar-page__months">
                            @foreach ($records_by_month as $month => $month_data)
                                <a href="{{typed_route('records.[RECORD].calendar.month', $is_radio, [$year, $month])}}" class="calendar-page__month">
                                    <span class="calendar-page__month__value">{{$month_data['name']}}</span>
                                    <span class="calendar-page__month__count">{{$month_data['count']}}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            <div class="col col--sidebar">
                @include('blocks.global.generic-sidebar', ['hide_articles' => true, 'is_radio' => $is_radio])
            </div>
        </div>

    </div>
@endsection
