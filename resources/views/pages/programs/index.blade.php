@extends('layouts.default')
@section('content')
    <div class="col">
        <div class="box">
            <div class="box__heading">
                <h1 class="box__heading__inner">{{$page_title}}</h1>
            </div>
            <div class="box__inner">
                <div class="channel-page__programs">
                    <div class="categories-list">
                        <a class="category @if (!$category) category--active @endif"
                           href="{{typed_route('records.[RECORD].programs', $is_radio)}}">
                            Все
                        </a>
                        @foreach($categories as $category_item)
                            <a class="category @if ($category && $category_item->id == $category->id) category--active @endif"
                               href="{{typed_route('records.[RECORD].programs', $is_radio, ['category' => $category_item->url])}}">
                                {{$category_item->name}}
                            </a>
                        @endforeach
                    </div>
                    <div class="horisontal-delimiter"></div>
                    <div class="categories-list">
                        <a class="category @if (!$period) category--active @endif"
                           href="{{request()->url()}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc(request()->query(), ['page','period']))}}">Все</a>
                        @foreach(\App\Constants\Periods::LIST as $period_item)
                            <a class="category @if ($period && $period['url'] == $period_item['url']) category--active @endif"
                               href="{{request()->url()}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc(request()->query(), ['page', 'year']), ['period' => $period_item['url']]))}}">{{$period_item['name']}}</a>
                        @endforeach
                    </div>
                    <div class="programs-list__container">
                        <div
                            class="programs-list @if ($params['is_radio']) programs-list--radio @endif @if (!$period && count($records_conditions['program_id_in']) > 20) programs-list--with-show-more @endif">
                            @foreach ($programs as $program)
                                @include('blocks.programs.item', ['show_channels' => true, 'is_radio' => $params['is_radio']])
                            @endforeach

                        </div>
                        @if ($show_load_more_button && count($records_conditions['program_id_in']) > 20)
                            <div class="programs-list__show-more">
                                <a data-is-radio="{{$params['is_radio']}}"
                                   data-category="{{$category ? $category->url : null}}"
                                   data-period="{{$period ? $period['url'] : null}}" class="button">Показать ещё</a>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @include('blocks.records.list', ['conditions' => $records_conditions])

@endsection
