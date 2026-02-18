@extends('layouts.default')
@php($active_link = array_values(array_filter($links, function($link) {
    return $link['active'];
}))[0])
@section('page-title')
    Рейтинг пользователей: {{mb_strtolower($active_link['name'])}}
@endsection
@section('content')
    <div class="box">
        <div class="box__heading">
            <h1 class="box__heading__inner">
                Рейтинг пользователей: {{mb_strtolower($active_link['name'])}}
            </h1>
        </div>
        <div class="box__inner">
            <div class="users-top">
                <div class="users-top__links">
                    @foreach ($links as $link)
                        <a class="users-top__link @if ($link['active']) users-top__link--active @endif" href="{{$link['url']}}">{{$link['name']}}</a>
                    @endforeach
                </div>
                <div class="users-top__values">
                    @yield('top-list-content')
                </div>
            </div>
        </div>
    </div>

@endsection
