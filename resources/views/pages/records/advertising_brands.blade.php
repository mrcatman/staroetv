@extends('layouts.default')
@section('page-title')
    Поиск рекламных роликов по брендам
@endsection
@section('content')
    <div class="inner-page commercials-list-page">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="{{$is_radio ? '/radio' : '/video'}}">Архив</a>
            <a class="breadcrumbs__item" href="{{$is_radio ? '/radio' : '/video'}}/commercials">Рекламные ролики</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">По бренду</a>
        </div>
        <div class="inner-page__header">
            <div class="inner-page__header__title">Поиск рекламных роликов по брендам</div>
            <div class="inner-page__header__right">
                <form action="{{$base_url}}" class="small-search-form">
                    @csrf
                    <input class="input" name="search" @if ($search) value="{{$search}}" @endif placeholder="Поиск">
                    <button class="button button--light" type="submit">Найти</button>
                </form>

            </div>
        </div>
        <div class="inner-page__content inner-page__content--no-padding">
            <div class="box">
                <div class="box__inner">
                   <div class="commercials-brands">
                       @foreach ($brands as $brand)
                           <a class="commercials-brands__item" href="{{$base_url}}?id={{$brand->id}}">
                               <div class="commercials-brands__item__cover" style="background-image:url({{$brand->cover}})"></div>
                               <div class="commercials-brands__item__name">
                                   @if ($search != '')
                                       {!! \App\Helpers\HighlightHelper::highlight($brand->advertising_brand, $search, true) !!}
                                   @else
                                       {{$brand->advertising_brand}}
                                   @endif
                               </div>


                           </a>
                       @endforeach
                   </div>
                </div>
                <div class="comments__pager">
                    {{$brands->links()}}
                </div>
            </div>
        </div>
    </div>
@endsection
