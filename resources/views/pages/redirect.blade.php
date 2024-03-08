@extends('layouts.default')
@section('content')
<div class="inner-page">
    <div class="box">
        <div class="box__heading">
            Переход по внешней ссылке
        </div>
        <div class="box__inner">
            <div style="font-size: 1.325em">
                Если вы уверены, что хотите перейти по ссылке <strong>{{$path}}</strong>, нажмите <a href="{{$path}}">сюда</a>
            </div>

        </div>
    </div>
</div>
@endsection

