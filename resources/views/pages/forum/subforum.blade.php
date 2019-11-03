@extends('layouts.default')
@section('content')
    <div class="forum-page">
        <div class="forum-section">
            <div class="forum-section__breadcrumbs">
                <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                @if ($parent_forum) <a class="forum-section__breadcrumb" href="/forum/{{$parent_forum->id}}">{{$parent_forum->title}}</a> @endif
                <a class="forum-section__breadcrumb" >{{$forum->title}}</a>
            </div>
            <div class="forum-section__title">
                <div class="forum-section__title__inner">{{$forum->title}}</div>
                <div class="forum-section__title__buttons">
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
            <div class="forum-section__children">
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
                    <div class="forum-section__subsection__title">Темы форума</div>
                    <div class="forum-section__subsection__children">
                        @foreach ($topics as $topic)
                             @include('blocks/forum_topic', ['topic' => $topic])
                        @endforeach
                    </div>
                </div>
                @endif
                @if (count($forum->subforums) > 0)
                   @foreach ($forum->subforums as $subforum)
                      @include('blocks/subforum', ['$subforum' => $subforum])
                   @endforeach
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
