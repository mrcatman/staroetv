@extends('layouts.default')
@section('page-title')
    @if ($user)
        Комментарии пользователя {{$user->username}}
    @else
        Последние комментарии на сайте
    @endif
@endsection
@section('content')
    <div class="box">
        <div class="box__heading">
            @if ($user)
                <h1 class="box__heading__inner">Комментарии пользователя&nbsp;<a
                        href="{{$user->url}}">{{$user->username}}</a></h1>
            @else
                <h1 class="box__heading__inner">Последние комментарии на сайте</h1>
            @endif
        </div>
        <div class="box__inner">
            <div class="comments">
                @foreach ($comments as $comment)
                    @include('blocks.comments.item', ['show_link' => true, 'comment' => $comment])
                @endforeach
            </div>
        </div>
        <div class="box__pager">
            {{$comments->appends(request()->except('_token'))->links()}}
        </div>

    </div>

@endsection
