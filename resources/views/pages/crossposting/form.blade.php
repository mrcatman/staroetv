@extends('layouts.default', ['vue' => true])
@section('page-title')
Посты в соцсетях
@endsection
@section('content')
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                {{ $crosspost ? "Изменить пост" : "Новый пост в соцсетях" }}
            </div>

            <div class="box__heading__right">
                <a href="{{route('crossposts.index')}}" class="button button--light">Назад</a>
            </div>
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <crossposts-manager :crosspost='@json($crosspost)' :services='@json($services)'/>
        </div>
        @csrf
    </div>
@endsection
