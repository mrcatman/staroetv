@extends('layouts.default')
@section('page-title')
    {{$year}} год - Календарь
@endsection
@section('content')
    <div class="inner-page calendar-page">
        <div class="breadcrumbs">
           <a class="breadcrumbs__item" href="/video">Архив</a>
            <a class="breadcrumbs__item" href="/video/calendar">Календарь</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">{{$year}}</a>
        </div>
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="inner-page__header">
                    <div class="inner-page__header__title">Все записи за {{$year}} год</div>
                </div>
                <div class="inner-page__content inner-page__content--no-padding">
                    <div class="row">
                        <div class="box">
                            <div class="box__inner">
                                <div class="calendar-page__months">
                                    @foreach ($records_by_month as $month => $month_data)
                                    <a href="/video/calendar/{{$year}}/{{$month}}" class="calendar-page__month">
                                        <span class="calendar-page__month__value">{{$month_data['name']}}</span>
                                        <span class="calendar-page__month__count">{{$month_data['count']}}</span>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col col--sidebar">
                @include('blocks/generic_sidebar', ['hide_articles' => true, 'is_radio' => false])
            </div>
        </div>

    </div>
@endsection
