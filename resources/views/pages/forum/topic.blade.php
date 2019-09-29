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
            <div class="forum-section__title">{{$topic->title}}</div>
            @if ($show_pager)
            <div class="forum-section__pager">
                {{$paginator->links()}}
            </div>
            @endif
            <div class="forum-section__messages">
                @if ($fixed_message)
                @include('blocks/forum_message', ['fixed' => true, 'message' => $fixed_message])
                @endif
                @foreach ($messages as $message)
                    @include('blocks/forum_message', ['fixed' => false, 'message' => $message])
                @endforeach
            </div>
            <div class="forum-section__breadcrumbs">
                <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                @if ($forum) <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a> @endif
                @if ($subforum) <a class="forum-section__breadcrumb" href="/forum/{{$subforum->id}}">{{$subforum->title}}</a> @endif
                <a class="forum-section__breadcrumb" >{{$topic->title}}</a>
            </div>
            @if ($show_pager)
                <div class="forum-section__pager">
                    {{$paginator->links()}}
                </div>
            @endif
            <div class="forum-section__form">
                @if (\App\Helpers\PermissionsHelper::allows("frreply") || \App\Helpers\PermissionsHelper::allows("frcloset"))
                @include('blocks/forum_form', ['topic_id' => $topic->id])
                @endif
            </div>
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