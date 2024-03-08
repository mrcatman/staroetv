@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">Поиск записей</div>
        </div>
        <div class="inner-page__content inner-page__content--no-padding">
            @include('blocks/records_search', ['is_radio' => $is_radio, 'params' => $params, 'show_results' => true, 'results' => $records, 'programs' => $programs])
        </div>
    </div>
@endsection
