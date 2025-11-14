@extends('blocks.top-list.wrapper')
@section('top-list-content')
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
@endsection
