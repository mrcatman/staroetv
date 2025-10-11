@extends('layouts.default')
@section('content')
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">{{$page_title}}</div>
        </div>
        <div class="box__inner">
            <div class="channel-page__programs">
                <div class="categories-list">
                    <a class="category @if (!$period) category--active @endif"
                       href="{{request()->url()}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc(request()->query(), ['period']))}}">Все</a>
                    @foreach(\App\Constants\Periods::LIST as $period_item)
                        <a class="category @if ($period && $period['url'] == $period_item['url']) category--active @endif"
                           href="{{request()->url()}}?{{http_build_query(array_merge(request()->query(), ['period' => $period_item['url']]))}}">{{$period_item['name']}}</a>
                    @endforeach
                </div>
                <div
                    class="programs-list @if (!$period && count($records_conditions['program_id_in']) > 20) programs-list--with-show-all @endif">
                    @include('blocks/programs_list', ['programs' => $programs])
                    @if (!$period && count($records_conditions['program_id_in']) > 20)
                        <div class="programs-list__show-all"><a data-is-radio="{{$params['is_radio']}}"
                                                                data-category="{{$category ? $category->url : null}}"
                                                                class="button">Показать все</a></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('blocks/records.list', ['conditions' => $records_conditions])

@endsection
