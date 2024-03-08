@extends('layouts.default')
@section('content')
    <form class="form box" method="POST">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="{{$channel->is_radio ? "/radio" : "/video"}}">Архив</a>
            <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">Список программ</a>
        </div>
        <div class="box__heading">
            Редактировать список программ
        </div>
        <div class="inner-page__content">
            <programs-manager :channel='{{$channel}}' :genres='@json($genres)' :programs='@json($programs)' />
        </div>
    </form>
@endsection
