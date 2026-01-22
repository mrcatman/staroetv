@extends('layouts.default')
@section('page-title')
    {{$month_name}} {{$year}} - Календарь
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
                            <a class="breadcrumbs__item" href="{{typed_route('records.[RECORD].calendar.year', $is_radio, $year)}}">{{$year}}</a>
                            <a class="breadcrumbs__item breadcrumbs__item--current">{{$month_name}}</a>
                        </div>
                    </div>
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            {{$month_name_full}}
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="calendar-page__days">
                            @foreach ($records_by_day as $day => $data)
                                <div class="calendar-page__day">
                                    @if ($day > 0)
                                        <div
                                            class="calendar-page__day__title">{{$day}} {{ $month_name_parental_case }}</div>
                                    @else
                                        <div class="calendar-page__day__title">Не указан день</div>
                                    @endif
                                    <div class="calendar-page__day__records">
                                        @foreach ($data as $channel_id => $records)
                                            <div class="calendar-page__day__group">
                                                @if (isset($channels_by_id[$channel_id]) > 0)
                                                    <a href="{{$channels_by_id[$channel_id]->full_url}}"
                                                       class="calendar-page__channel">
                                                    <span class="calendar-page__channel__logo"
                                                          style="background-image:url({{$channels_by_id[$channel_id]->logo_path}})"></span>
                                                        <span
                                                            class="calendar-page__channel__name">{{$channels_by_id[$channel_id]->name}}</span>
                                                    </a>
                                                @else
                                                    <a class="calendar-page__channel">
                                                        <span class="calendar-page__channel__name">Не указан канал</span>
                                                    </a>
                                                @endif

                                                <div class="calendar-page__records">
                                                    <div class="records-list @if (!$is_radio) records-list--thumbs @endif">
                                                        @foreach ($records as $record)
                                                            @include($record->is_radio ? 'blocks.records.radio-item' : 'blocks.records.item')
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                        @endforeach
                                    </div>
                                </div>
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
