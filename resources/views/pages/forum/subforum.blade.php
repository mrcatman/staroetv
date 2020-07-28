@extends('layouts.default')
@section('content')
    <div class="forum-page">
        <div class="forum-section">
            @include('blocks/forum_buttons')
            <div class="forum-section__breadcrumbs forum-section__breadcrumbs--with-search">
                <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                @if ($parent_forum) <a class="forum-section__breadcrumb" href="/forum/{{$parent_forum->id}}">{{$parent_forum->title}}</a> @endif
                @if (!$forum && $search)
                    <a class="forum-section__breadcrumb" href="/forum/">Поиск: {{$search}}</a>
                @elseif ($forum)
                    <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a>
                @endif
                <form @if (!$forum) action="/forum" @else action="/forum/{{$forum->id}}" @endif method="GET" class="forum-section__search forum-section__search--subforum">
                    <input @if ($forum) placeholder="Поиск по подфоруму" @else placeholder="Поиск по форуму" @endif class="input" name="s" value="{{$search}}">
                    <select class="select-classic" name="type">
                        <option value="topics" @if (!isset($messages_view) || !$messages_view) selected @endif>Темы</option>
                        <option value="messages" @if (isset($messages_view) && $messages_view) selected @endif>Сообщения</option>
                    </select>
                    <button type="submit" class="button button--light">ОК</button>
                </form>
            </div>
            @if ($forum)
            <div class="forum-section__title">
                <div class="forum-section__title__inner">{{$forum->title}}</div>
                <div class="forum-section__title__buttons">
                    <div class="buttons-row">
                        @if ($forum->can_create_new_topic)
                            <a class="button" href="/forum/{{$forum->id}}/new-topic">Создать тему</a>
                        @endif
                        @if ($forum->parent_id < 1 && \App\Helpers\PermissionsHelper::allows('fredit'))
                            <a class="button" href="/forum/{{$forum->id}}/new">Новый подфорум</a>
                        @endif
                        @if (\App\Helpers\PermissionsHelper::allows('fredit'))
                            <a class="button" href="/forum/edit/{{$forum->id}}">Редактировать форум</a>
                        @endif
                    </div>

                </div>
            </div>
            @elseif (isset($title))
                <div class="forum-section__title">
                    <div class="forum-section__title__inner">{!! $title !!}</div>
                </div>
            @endif
            @if ($messages_view)
                <div class="forum-section__pager-container">
                    <div class="forum-section__pager">
                        {{$paginator->links()}}
                    </div>
                </div>
            @endif
            <div class="forum-section__children">
                @if (isset($messages_view) && $messages_view)
                    @foreach ($messages as $message)
                        @include('blocks/forum_message', ['fixed' => false, 'message' => $message, 'highlight' => $search])
                    @endforeach
                @else
                @if (count($fixed_topics) > 0)
                <div class="forum-section__subsection">
                    <div class="forum-section__subsection__title">Важные темы</div>
                    <div class="forum-section__subsection__children">
                        @foreach ($fixed_topics as $topic)
                            @include('blocks/forum_topic', ['topic' => $topic])
                        @endforeach
                    </div>
                </div>
                @endif
                @if (count($topics) > 0)
                <div class="forum-section__subsection">
                    @if (!isset($title))
                    <div class="forum-section__subsection__title">Темы форума</div>
                    @endif
                    <div class="forum-section__subsection__children">
                        @foreach ($topics as $topic)
                             @include('blocks/forum_topic', ['topic' => $topic])
                        @endforeach
                    </div>
                </div>
                @endif
                @if ($forum && count($forum->subforums) > 0 && (!$search || $search == ""))
                   @foreach ($forum->subforums as $subforum)
                      @include('blocks/subforum', ['$subforum' => $subforum])
                   @endforeach
                @endif
                @endif
            </div>
            <div class="forum-section__pager-container">
                <div class="forum-section__pager">
                    {{$paginator->links()}}
                </div>
            </div>
        </div>
    </div>
@endsection
