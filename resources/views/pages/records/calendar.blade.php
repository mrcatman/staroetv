@extends('layouts.default')
@section('page-title')
    Календарь
@endsection
@section('content')
    <div class="inner-page calendar-page">
        <div class="breadcrumbs">
           <a class="breadcrumbs__item" href="/video">Архив</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">Календарь</a>
        </div>
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="inner-page__header">
                    <div class="inner-page__header__title">Записи по годам</div>
                </div>
                <div class="inner-page__content inner-page__content--no-padding">
                    <div class="row">
                        <div class="box">
                            <div class="box__inner">
                                <div class="calendar-page__years">
                                    @foreach ($years as $year)
                                    <a href="/video/calendar/{{$year->year}}" class="calendar-page__year">
                                        <span class="calendar-page__year__value">{{$year->year}}</span>
                                        <span class="calendar-page__year__count">{{$year->count_year}}</span>
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
