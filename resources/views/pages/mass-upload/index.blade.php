@extends('layouts.default', ['vue' => true])
@section('content')
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                Массовая загрузка {{$is_radio ? "аудио" : "видео"}}
            </div>

        </div>
        <div class="box__inner">
            <mass-uploader :is-radio="{{$is_radio ? "true" : "false"}}"></mass-uploader>
        </div>

    </div>
@endsection
