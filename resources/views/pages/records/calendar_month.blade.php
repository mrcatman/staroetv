@extends('layouts.default')
@section('page-title')
    {{$month_name}} - Календарь
@endsection
@section('content')
    <div class="inner-page calendar-page">
        <div class="breadcrumbs">
           <a class="breadcrumbs__item" href="/video">Архив</a>
            <a class="breadcrumbs__item" href="/video/calendar">Календарь</a>
            <a class="breadcrumbs__item" href="/video/calendar/{{$year}}">{{$year}}</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">{{$month_name}}</a>
        </div>
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="inner-page__header">
                    <div class="inner-page__header__title">{{$month_name_full}}</div>
                </div>
                <div class="inner-page__content inner-page__content--no-padding">
                    <div class="row">
                        <div class="box">
                            <div class="box__inner">
                                <div class="calendar-page__days">
                                    @foreach ($records_by_day as $day => $data)
                                    <div class="calendar-page__day">
                                        @if ($day > 0)
                                            <div class="calendar-page__day__title">{{$day}} {{ $month_name_parental_case }}</div>
                                        @else
                                            <div class="calendar-page__day__title">Неизвестный день</div>
                                        @endif
                                        <div class="calendar-page__day__records">
                                            @foreach ($data as $channel_id => $records)
                                                @if (isset($channels_by_id[$channel_id]) > 0)
                                                    <a href="{{$channels_by_id[$channel_id]->full_url}}" class="calendar-page__channel">
                                                        <span class="calendar-page__channel__logo" style="background-image:url({{$channels_by_id[$channel_id]->logo_path}})"></span>
                                                        <span class="calendar-page__channel__name">{{$channels_by_id[$channel_id]->name}}</span>
                                                    </a>
                                                @else
                                                    <a class="calendar-page__channel">
                                                        <span class="calendar-page__channel__name">Неизвестный канал</span>
                                                    </a>
                                                @endif

                                                <div class="calendar-page__records">
                                                    @foreach ($records as $record)
                                                        <a target="_blank" href="{{$record->url}}" class="calendar-page__record">
                                                            <span class="calendar-page__record__cover" style="background-image:url({{$record->cover}})"></span>
                                                            <span class="calendar-page__record__title">
                                                                {{$record->title}}
                                                            </span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
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
