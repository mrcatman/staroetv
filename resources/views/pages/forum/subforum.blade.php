@extends('layouts.default')
@section('page-title')
    @if ($forum)
        {{$forum->title}}
    @elseif (isset($title))
        {{strip_tags($title)}}
    @endif
@endsection
@section('content')
    <div class="forum-page">
        <div class="forum-section">
            <div class="forum__top-panel__outer">
                <div class="forum__top-panel">
                    <div class="forum__top-panel__inner">
                        @include('blocks.forum.buttons')
                        <div class="forum-section__breadcrumbs forum-section__breadcrumbs--with-search">
                            <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                            @if ($parent_forum)
                                <a class="forum-section__breadcrumb"
                                   href="/forum/{{$parent_forum->id}}">{{$parent_forum->title}}</a>
                            @endif
                            @if (!$forum && $search)
                                <a class="forum-section__breadcrumb" href="/forum/">Поиск: {{$search}}</a>
                            @elseif ($forum)
                                <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a>
                            @endif
                            <form @if (!$forum) action="/forum" @else action="/forum/{{$forum->id}}" @endif method="GET"
                                  class="forum-section__search forum-section__search--subforum">
                                <input @if ($forum) placeholder="Поиск по подфоруму" @else placeholder="Поиск по форуму"
                                       @endif class="input" name="s" value="{{$search}}">
                                <select class="select-classic" name="type">
                                    <option value="topics"
                                            @if (!isset($messages_view) || !$messages_view) selected @endif>Темы
                                    </option>
                                    <option value="messages"
                                            @if (isset($messages_view) && $messages_view) selected @endif>Сообщения
                                    </option>
                                </select>
                                <button type="submit" class="button button--light">ОК</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if ($messages_view)
                <div class="box">
                    <div class="box__pager">

                        <div class="forum-section__pager">
                            {{$paginator->links()}}
                        </div>
                    </div>
                </div>
            @endif
            <div class="forum-section__children">
                @if ($forum && count($forum->subforums) > 0 && (!$search || $search == ""))
                    <div class="box">
                        <div class="box__heading">
                            <div class="box__heading__inner">Подфорумы</div>
                        </div>
                        <div class="box__inner">
                            @foreach ($forum->subforums as $subforum)
                                @include('blocks/subforum', ['$subforum' => $subforum])
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (isset($messages_view) && $messages_view)
                    @foreach ($messages as $message)
                        @include('blocks.forum.message', ['fixed' => false, 'message' => $message, 'highlight' => $search])
                    @endforeach
                @else
                    @if (count($fixed_topics) > 0)
                        <div class="box">
                            <div class="box__heading">
                                <div class="box__heading__inner">Важные темы</div>
                            </div>
                            <div class="box__inner">
                                @foreach ($fixed_topics as $topic)
                                    @include('blocks.forum.topic', ['topic' => $topic])
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if (count($topics) > 0)
                        <div class="box">
                            @if (!isset($title))
                                <div class="box__heading">
                                    <div class="box__heading__inner">Темы форума</div>
                                    <div class="box__heading__buttons">
                                        <div class="buttons-row">
                                            @if ($forum->can_create_new_topic)
                                                <a class="button" href="/forum/{{$forum->id}}/new-topic">
                                                    <i class="fa fa-plus"></i>
                                                    Создать тему
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="box__heading__right">
                                        <div class="buttons-row">
                                            @if ($forum->parent_id < 1 && \App\Helpers\PermissionsHelper::allows('fredit'))
                                                <a class="button" href="/forum/{{$forum->id}}/new">
                                                    <i class="fa fa-plus"></i>
                                                    Новый подфорум
                                                </a>
                                            @endif
                                            @if (\App\Helpers\PermissionsHelper::allows('fredit'))
                                                <a class="button" href="/forum/edit/{{$forum->id}}">
                                                    <i class="fa fa-edit"></i>
                                                    Редактировать форум
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="box__inner">
                                @foreach ($topics as $topic)
                                    @include('blocks.forum.topic', ['topic' => $topic])
                                @endforeach
                            </div>
                            <div class="box__pager">
                                {{$paginator->links()}}
                            </div>
                        </div>
                    @endif

                @endif
            </div>
        </div>
    </div>
@endsection
