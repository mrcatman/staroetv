@extends('layouts.default')
@section('content')
    <div class="inner-page advertising-list-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">Архив рекламных роликов</div>
            <div class="inner-page__header__right">

            </div>
        </div>
        <div class="inner-page__content inner-page__content--no-padding">
            <div class="channels-list-page__tabs">
                <div class="top-list">
                    <a class="top-list__item top-list__item--all @if (!$selected_year) top-list__item--active @endif" href="{{$base_link}}">
                        <span class="top-list__item__name">Все</span>
                        <span class="top-list__item__count">{{$total_count}}</span>
                    </a>
                    @foreach ($years as $year => $count)
                        <a class="top-list__item @if ($selected_year == $year) top-list__item--active @endif" href="{{$base_link}}?year={{$year}}">
                            <span class="top-list__item__name">{{$year}}</span>
                            <span class="top-list__item__count">{{$count}}</span>
                        </a>
                    @endforeach
                    <a class="top-list__item @if ($selected_year == "0") top-list__item--active @endif" href="{{$base_link}}?year=0">
                        Не указано {{$other_count}}
                    </a>
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
@endsection