@extends('layouts.default')
@section('page-title')
Посты в соцсетях
@endsection
@section('content')
    <div class="box">
        <div class="box__heading">
            {{ $crosspost ? "Изменить пост" : "Новый пост в соцсетях" }}
            <div class="box__heading__right">
                <a href="/crossposts" class="button">Назад</a>
            </div>
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <crossposts-manager :crosspost='@json($crosspost)' :services='@json($services)'/>
        </div>
        @csrf
    </div>
@endsection
