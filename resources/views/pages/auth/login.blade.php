@extends('layouts.default')
@section('content')
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                Вход на сайт
            </div>

        </div>
        <div class="box__inner">
            @include('blocks.auth.login-form', ['modal' => false])
        </div>
    </div>
@endsection
