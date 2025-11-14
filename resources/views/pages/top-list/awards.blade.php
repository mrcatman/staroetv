@extends('blocks.top-list.wrapper')
@section('top-list-content')
    @foreach($list as $row)
        <div class="users-top__row users-top__row--awards">
            @if ($row['award']->picture)
                <img class="users-top__row__award" src="{{$row['award']->picture->url}}"/>
            @endif
            @foreach ($row['users'] as $user)
                <span class="users-top__row__awards-user" style="font-size: {{.875 + ($user['count'] / 5)}}em">
                    <a target="_blank" class="users-top__row__user" href="{{$user['url']}}">{{$user['username']}}</a>
                    <span class="users-top__row__count">{{$user['count']}}</span>
                </span>
            @endforeach
        </div>
    @endforeach

@endsection
