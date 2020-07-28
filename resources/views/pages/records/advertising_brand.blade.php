@extends('layouts.default')
@section('page-title')
    {{$brand}} - Рекламные ролики
@endsection
@section('content')
    <div class="inner-page commercials-list-page">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="{{$is_radio ? '/radio' : '/video'}}">Архив</a>
            <a class="breadcrumbs__item" href="{{$is_radio ? '/radio' : '/video'}}/commercials">Рекламные ролики</a>
            <a class="breadcrumbs__item" href="{{$is_radio ? '/radio' : '/video'}}/commercials-search">По бренду</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">{{$brand}}</a>
        </div>
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$brand}}</div>
        </div>
        @include('blocks/records_list', ['class' => 'records-list__outer--full-page', 'conditions' => $records_conditions, 'title_param' => 'short_title'])
    </div>
@endsection
