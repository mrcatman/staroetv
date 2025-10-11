@extends('layouts.default', ['vue' => true])
@section('content')
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                Массовая загрузка видео из источника
            </div>

        </div>
        <div class="box__inner">
            <mass-uploader :show-files="{{$can_upload ? "true" : "false"}}"></mass-uploader>
        </div>

    </div>
@endsection
