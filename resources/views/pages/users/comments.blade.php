@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">Комментарии пользователя <a href="{{$user->url}}">{{$user->username}}</a></div>
        </div>
        <div class="inner-page__content">
            <div class="row">
                <div class="col">
                    @foreach ($comments as $comment)
                        @include('blocks/comment', ['show_link' => true, 'comment' => $comment])
                    @endforeach
                    <div class="pager-container pager-container--light">
                        {{$comments->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
