@extends('layouts.default', ['vue' => true])
@section('content')
    <div class="box">
        <div class="box__heading">
            Массовая загрузка видео с устройства
        </div>
        <div class="box__inner">
            <upload-from-device></upload-from-device>
        </div>

    </div>
@endsection
