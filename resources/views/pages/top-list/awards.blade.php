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
                    @foreach($list as $row)
                    <div class="users-top__row users-top__row--awards">
                        @if ($row['award']->picture)
                        <img class="users-top__row__award" src="{{$row['award']->picture->url}}" />
                        @endif
                        @foreach ($row['users'] as $user)
                            <span class="users-top__row__awards-user" style="font-size: {{.875 + ($user['count'] / 5)}}em">
                                <a target="_blank" class="users-top__row__user" href="{{$user['url']}}">{{$user['username']}}</a>
                                <span class="users-top__row__count">{{$user['count']}}</span>
                            </span>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
