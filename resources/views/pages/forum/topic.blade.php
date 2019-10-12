@extends('layouts.default')
@section('content')
    <div class="forum-page">
        <div class="forum-section" data-forum-id="{{$topic->forum_id}}" data-topic-id="{{$topic->id}}">
            <div class="forum-section__breadcrumbs">
                <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                @if ($forum) <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a> @endif
                @if ($subforum) <a class="forum-section__breadcrumb" href="/forum/{{$subforum->id}}">{{$subforum->title}}</a> @endif
                <a class="forum-section__breadcrumb" >{{$topic->title}}</a>
            </div>
            <div class="forum-section__title">
                <div class="forum-section__title__inner">
                    @if ($topic->is_closed)<i class="fa fa-lock"></i>@endif
                    {{$topic->title}}
                </div>
                <div class="forum-section__title__buttons">
                    @include('blocks/forum_topic_panel', ['topic' => $topic])
                </div>
            </div>
            @if ($show_pager)
            <div class="forum-section__pager-container">
                <div class="forum-section__pager">
                    {{$paginator->links()}}
                </div>
                <form action="/forum/{{$topic->forum_id}}-{{$topic->id}}-1" method="GET" class="forum-section__search">
                    <input class="input" name="s" value="{{$search}}">
                    <button type="submit" class="button button--light">ОК</button>
                </form>
            </div>
            @endif
            <div class="forum-section__messages">
                @if ($fixed_message)
                    @include('blocks/forum_message', ['fixed' => true, 'message' => $fixed_message])
                @endif
                @foreach ($messages as $message)
                    @include('blocks/forum_message', ['fixed' => false, 'message' => $message, 'highlight' => $search])
                @endforeach
            </div>
            <div class="forum-section__breadcrumbs">
                <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                @if ($forum) <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a> @endif
                @if ($subforum) <a class="forum-section__breadcrumb" href="/forum/{{$subforum->id}}">{{$subforum->title}}</a> @endif
                <a class="forum-section__breadcrumb" >{{$topic->title}}</a>
            </div>
            @if ($show_pager)
                <div class="forum-section__pager-container">
                    <div class="forum-section__pager">
                        {{$paginator->links()}}
                    </div>
                    <form action="/forum/{{$topic->forum_id}}-{{$topic->id}}-1" method="GET" class="forum-section__search">
                        <input class="input" name="s" value="{{$search}}">
                        <button type="submit" class="button button--light">ОК</button>
                    </form>
                </div>
            @endif
            @if (\App\Helpers\PermissionsHelper::allows("frreply") || \App\Helpers\PermissionsHelper::allows("frcloset"))
                <div class="forum-section__form">
                @include('blocks/forum_form', ['topic_id' => $topic->id])
                </div>
            @endif
        </div>
    </div>
    @include('blocks/change_reputation_modal')

    @foreach ($users as $user)
        <div id="reputation_history_{{$user->id}}" data-title="Репутация пользователя {{$user->username}} ({{$user->reputation_number}})" style="display:none"></div>
        <div id="warnings_history_{{$user->id}}" data-title="Баны пользователя {{$user->username}} ({{$user->ban_level}}%)" style="display:none"></div>
        <div id="awards_history_{{$user->id}}" data-title="Награды пользователя {{$user->username}} ({{count($user->awards)}})" style="display:none"></div>
    @endforeach
@endsection
@section ('scripts')
    <script>
        var bb = new bbCodes();
        bb.init('message');
    </script>
@endsection