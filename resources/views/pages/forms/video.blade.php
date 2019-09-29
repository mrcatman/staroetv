@extends('layouts.default')
@section('content')
    <form class="box" method="POST">
        <div class="box__heading">
            {{$video ? "Редактировать видео" : "Добавить видео"}}
            <div class="box__heading__right">
                @if ($video)
                <a href="{{$video->url}}" class="box__heading__link">Назад</a>
                @else
                <a href="/videos" class="box__heading__link">Назад</a>
                @endif
            </div>
        </div>
        <div class="box__inner">
            <video-form :channels='{!! json_encode($channels) !!}' :video='{!! json_encode($video) !!}'></video-form>
        </div>
        @csrf
    </form>
@endsection
