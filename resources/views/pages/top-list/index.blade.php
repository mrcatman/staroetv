@extends('layouts.default')
@section('content')
    <div class="box">
        <div class="box__heading">
           Рейтинг активности пользователей по разделам
        </div>
        <div class="box__inner">
            <div class="users-top">
                <div class="users-top__links">
                    @foreach ($links as $url => $name)
                    <a class="users-top__link @if (request()->path() == 'top-list/'.$url) users-top__link--active @endif" href="/top-list/{{$url}}">{{$name}}</a>
                    @endforeach
                </div>
                <div class="users-top__values">
                    @foreach($list as $index => $row)
                    <div class="users-top__row">
                        <span class="users-top__row__position">#{{$index + 1}}</span>
                        <a target="_blank" class="users-top__row__user" href="{{$row['user']->url}}">{{$row['user']->username}}</a>
                        @if ($row['user']->group)
                        <span class="users-top__row__user-group">({{$row['user']->group->name}})</span>
                        @endif
                        {{$row['text']}}
                        <span class="users-top__row__value">{{$row['value']}}</span>
                        {{$row['after_text']}}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
