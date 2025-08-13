@extends('layouts.default')
@section('content')
    <div class="box">
        <div class="box__heading">
            Массовая загрузка видео из источника
        </div>
        <div class="box__inner">
            <mass-uploader :show-files="{{$can_upload ? "true" : "false"}}"></mass-uploader>
        </div>

    </div>
@endsection
