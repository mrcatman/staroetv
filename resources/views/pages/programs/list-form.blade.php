@extends('layouts.default', ['vue' => true])
@section('content')
    <form class="form box" method="POST">
        <div class="box__breadcrumbs">
            <div class="breadcrumbs">
                <a class="breadcrumbs__item" href="{{$channel->is_radio ? "/radio" : "/video"}}">Архив</a>
                <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
                <a class="breadcrumbs__item breadcrumbs__item--current">Список программ</a>
            </div>
        </div>

        <div class="box__heading">
            <div class="box__heading__inner">
                Редактировать список программ
            </div>
        </div>
        <div class="box__inner">
            <programs-manager :channel='{{$channel}}' :genres='@json($genres)' :programs='@json($programs)' />
        </div>
    </form>
@endsection
