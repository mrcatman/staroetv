@extends('layouts.default')
@section('content')
    <form class="box" method="POST">
        <div class="box__heading">
            {{$data['is_radio'] ? ($record ? "Редактировать радиозапись" : "Добавить радиозапись") :  ($record ? "Редактировать видео" : "Добавить видео")}}
            <div class="box__heading__right">
                @if ($record)
                <a href="{{$record->url}}" class="box__heading__link">Назад</a>
                @else
                <a href="/videos" class="box__heading__link">Назад</a>
                @endif
            </div>
        </div>
        <div class="box__inner">
            <record-form :can-upload="{{$can_upload ? "true" : "false"}}" :meta='@json($data)' :channels='@json($channels)' :record='@json($record)'></record-form>
        </div>
        @csrf
    </form>
@endsection
