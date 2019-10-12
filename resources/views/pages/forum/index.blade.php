@extends('layouts.default')
@section('content')
    <div class="forum-page">
        <div class="forum-section__title forum-section__title--main-page">
            <div class="forum-section__title__inner">Форум</div>
            <div class="forum-section__title__buttons">
                @if (\App\Helpers\PermissionsHelper::allows('fredit'))
                    <a class="button" href="/forum/0/new">Новый форум</a>
                @endif
            </div>
        </div>
        @foreach ($forums as $forum)
        <div class="forum-section">
            <div class="forum-section__title">{{$forum->title}}</div>
            <div class="forum-section__children">
                @foreach ($forum->subforums as $subforum)
                    @include('blocks/subforum', ['$subforum' => $subforum])
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
@endsection
