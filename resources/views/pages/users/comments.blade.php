@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">Комментарии пользователя <a href="{{$user->url}}">{{$user->username}}</a></div>
        </div>
        <div class="inner-page__content">
            <div class="box">
                <div class="box__inner">
                    <div class="comments">
                        @foreach ($comments as $comment)
                            @include('blocks/comment', ['show_link' => true, 'comment' => $comment])
                        @endforeach
                    </div>
                    <div class="comments__pager">
                        {{$comments->append(request()->except('_token'))->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
