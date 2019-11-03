@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">Поиск записей по запросу "{{$params['search']}}" <span class="box__heading__count">{{$records_count}}</span></div>
        </div>
        <div class="inner-page__content inner-page__content--no-padding">
            @include('blocks/records_search', ['is_radio' => $is_radio, 'params' => $params, 'show_results' => true, 'results' => $records])
        </div>
    </div>
@endsection