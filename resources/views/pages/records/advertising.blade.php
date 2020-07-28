@extends('layouts.default')
@section('page-title')
    Архив рекламных роликов
@endsection
@section('content')
    <div class="inner-page commercials-list-page">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="{{$is_radio ? '/radio' : '/video'}}">Архив</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">Рекламные ролики</a>
        </div>
        <div class="inner-page__header">
            <div class="inner-page__header__title">Архив рекламных роликов</div>
            <div class="inner-page__header__right">

            </div>
        </div>
        <div class="inner-page__content inner-page__content--no-padding">
            <div class="box">
                <div class="box__inner">
                    <div class="channels-list-page__tabs">

                        <div class="top-list">
                            <span class="top-list__heading">По годам</span>
                            <a class="top-list__item top-list__item--all @if (!$selected_years_range && !$selected_year) top-list__item--active @endif" href="{{$base_link}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($query_params, ['year', 'year_start', 'year_end']))}}">
                                <span class="top-list__item__name">Все</span>
                            </a>
                            @foreach ($years_ranges as $years_range => $params)
                                <a class="top-list__item @if ($selected_years_range == $years_range) top-list__item--active @endif" href="{{$base_link}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($query_params, ['year']), $params))}}">
                                    <span class="top-list__item__name">{{$years_range}}</span>
                                </a>
                            @endforeach
                        </div>
                        <div class="categories-list">
                            @foreach ($years as $year => $count)
                                <a class="category @if ($selected_year == $year) category--active @endif" @if ($selected_year == $year) href="{{$base_link}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($query_params, ['year', 'year_start', 'year_end']))}}" @else href="{{$base_link}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($query_params, ['year_start', 'year_end']), ['year' => $year]))}}" @endif>
                                    {{$year}}
                                    <span class="category__count">{{$count}}</span>
                                </a>
                            @endforeach
                            <a class="category @if ($selected_year === 0) category--active @endif" href="{{$base_link}}?{{http_build_query(array_merge(['year' => 0], \App\Helpers\ArraysHelper::diffAssoc($query_params, ['year_start', 'year_end'])))}}">
                                Не указано <span class="category__count">{{$other_count}}</span>
                            </a>
                        </div>
                        <!--
                        <div class="top-list">

                        </div>
                        -->
                        <div class="top-list">
                            <span class="top-list__heading">По типу</span>
                            <a class="top-list__item top-list__item--all @if (!$selected_type) top-list__item--active @endif" href="{{$base_link}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($query_params, ['type']))}}">
                                <span class="top-list__item__name">Все</span>
                            </a>
                            @foreach ($types as $type => $type_name)
                                <a class="top-list__item @if ($selected_type == $type) top-list__item--active @endif" href="{{$base_link}}?{{http_build_query(array_merge($query_params, ['type' => $type]))}}">
                                    <span class="top-list__item__name">{{$type_name}}</span>
                                </a>
                            @endforeach
                        </div>
                        <div class="top-list top-list--short">
                            <span class="top-list__heading">По регионам</span>

                            <a class="top-list__item top-list__item--all @if (!$selected_region) top-list__item--active @endif" href="{{$base_link}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($query_params, ['region']))}}">
                                <span class="top-list__item__name">Все</span>
                            </a>
                            @foreach ($regions as $region)
                                <a class="top-list__item @if ($selected_region == $region) top-list__item--active @endif" href="{{$base_link}}?{{http_build_query(array_merge($query_params, ['region' => $region]))}}">
                                    <span class="top-list__item__name">{{$region}}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="commercials-list-page__bottom">
                <a class="button button--light commercials-list-page__brands" href="{{$base_link}}-search">По бренду/товару/услуге</a>
                @include('blocks/records_list', ['class' => 'records-list__outer--full-page', 'conditions' => $records_conditions, 'title_param' => 'short_title'])
            </div>

        </div>
    </div>
@endsection
