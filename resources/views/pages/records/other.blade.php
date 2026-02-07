@extends('layouts.default')
@section('page-title')
    @if ($category)
        {{$category->name}}
    @else
        Прочее
    @endif
@endsection
@section('content')
    @php($route_prefix = route_prefix_records($is_radio))

    <div class="row row--align-start">
        <div class="col col--2-5">
            <div class="box">
                <div class="box__breadcrumbs">
                    <div class="breadcrumbs">
                        <a class="breadcrumbs__item" href="{{typed_route('records.[RECORD].index', $is_radio)}}">Архив</a>
                        <a class="breadcrumbs__item @if (!$category) breadcrumbs__item--current @endif"
                           @if ($category) href="{{$category->full_url}}" @endif>Прочее
                        </a>
                        @if ($category)
                            <a class="breadcrumbs__item breadcrumbs__item--current">{{$category->name}}</a>
                        @endif
                    </div>
                </div>
                <div class="box__heading">
                    <div class="box__heading__inner">
                        @if ($category)
                            {{$category->name}}
                        @else
                            Прочее
                        @endif
                    </div>
                </div>

                <div class="box__inner">
                    <div class="record-categories">
                        @foreach ($categories as $category_item)
                            <div class="record-categories__item-container">
                                <a class="record-categories__item @if ($category && isset($category_item->id) && $category->id == $category_item->id) record-categories__item--active @endif"
                                   href="{{ $category_item->full_url}}">
                                    {{$category_item->name}}
                                    <span class="record-categories__item__count">{{$category_item->records_count}}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="row">
                @include('blocks.records.list', ['conditions' => $records_conditions])
            </div>
        </div>


        <div class="col col--sidebar">
            @include('blocks.global.generic-sidebar', ['hide_articles' => true, 'is_radio' => $is_radio])
        </div>
    </div>
@endsection
