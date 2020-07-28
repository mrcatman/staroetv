@extends('layouts.default')
@section('page-title')
    @if ($category)
        {{$category->name}}
    @else
    Прочее
    @endif
@endsection
@section('content')
    <div class="inner-page">
        <div class="breadcrumbs">
           <a class="breadcrumbs__item" href="/video">Архив</a>
            <a class="breadcrumbs__item @if (!$category) breadcrumbs__item--current @endif" @if ($category) href="/video/other" @endif>Прочее</a>
            @if ($category)
            <a class="breadcrumbs__item breadcrumbs__item--current">{{$category->name}}</a>
            @endif
        </div>
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="inner-page__header">
                    @if ($category)
                        <div class="inner-page__header__title">{{$category->name}}</div>
                    @else
                        <div class="inner-page__header__title">Прочее</div>
                    @endif
                </div>
                <div class="inner-page__content inner-page__content--no-padding">
                    <div class="row">
                        <div class="box">
                            <div class="box__inner">
                                <div class="record-categories">
                                    @foreach ($categories as $category_item)
                                     <div class="record-categories__item-container">
                                         <a class="record-categories__item @if ($category && $category->id == $category_item->id) record-categories__item--active @endif" href="/video/other/{{$category_item->url}}">{{$category_item->name}} <span class="record-categories__item__count">{{$category_item->records_count}}</span></a>
                                     </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @include('blocks/records_list', ['conditions' => $records_conditions])
                    </div>
                </div>
            </div>

            <div class="col col--sidebar">
                @include('blocks/generic_sidebar', ['hide_articles' => true, 'is_radio' => $is_radio])
            </div>
        </div>

    </div>
@endsection
