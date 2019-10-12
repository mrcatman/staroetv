@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$page->title}}</div>
            <a class="button button--light" href="/pages/{{$page->id}}/edit">Редактировать</a>
        </div>
        <div class="inner-page__content">
            <div class="inner-page__text-block">
                {!! $page->content !!}
            </div>
        </div>
    </div>
@endsection