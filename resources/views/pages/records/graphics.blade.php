@extends('layouts.default')
@section('page-title')
    {{$page_title}}
@endsection
@section('content')
    <div class="inner-page interprogram-index-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="inner-page__header">
                    <div class="inner-page__header__title">{{$page_title}}</div>
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
                                    <div class="top-list__right">
                                        <a href="{{$base_link}}?{{http_build_query(array_merge($query_params, ['hide_commercials' => $hide_commercials ? 0 : 1]))}}" class="input-container input-container--checkbox">
                                            <input disabled type="checkbox" @if ($hide_commercials) checked="checked" @endif name="hide_commercials">
                                            <div class="input-container--checkbox__element"></div>
                                            <div class="input-container__label">Скрыть рекламные блоки и анонсы</div>
                                        </a>
                                    </div>
                                </div>
                                <div class="categories-list">
                                    @foreach ($years as $year => $count)
                                        <a class="category @if ($selected_year == $year) category--active @endif" @if ($selected_year == $year) href="{{$base_link}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($query_params, ['year', 'year_start', 'year_end']))}}" @else href="{{$base_link}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($query_params, ['year_start', 'year_end']), ['year' => $year]))}}" @endif>
                                            {{$year}}
                                            <span class="category__count">{{$count}}</span>
                                        </a>
                                    @endforeach
                                </div>
                                <!--
                                @if (!$is_radio)
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
                                @endif
                                -->
                                <div class="top-list top-list--short">
                                    <span class="top-list__heading">По регионам</span>
                                    <a class="top-list__item top-list__item--all @if (!request()->has('regional')) top-list__item--active @endif" href="{{$base_link}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($query_params, ['regional']))}}">
                                        <span class="top-list__item__name">Все</span>
                                    </a>
                                    <a class="top-list__item @if (request()->input('regional')  === '0') top-list__item--active @endif" href="{{$base_link}}?{{http_build_query(array_merge($query_params, ['regional' => 0]))}}">
                                        <span class="top-list__item__name">С федеральных каналов</span>
                                    </a>
                                    <a class="top-list__item @if (request()->input('regional')  === '1') top-list__item--active @endif" href="{{$base_link}}?{{http_build_query(array_merge($query_params, ['regional' => 1]))}}">
                                        <span class="top-list__item__name">С региональных каналов</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @include('blocks/records_list', ['class' => 'records-list__outer--full-page','conditions' => $records_conditions])
                    </div>
                </div>
            </div>
            <!--
            <div class="col col--sidebar">
            </div>
            -->
        </div>

    </div>
@endsection
