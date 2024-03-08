@extends('layouts.default')
@section('page-title')
    {{$topic->title}}
@endsection
@section('content')
    <div class="forum-page">
        <div class="forum-section" data-forum-id="{{$topic->forum_id}}" data-topic-id="{{$topic->id}}">
            <div class="forum__top-panel__outer">
                <div class="forum__top-panel">
                    <div class="forum__top-panel__inner">
                        <div class="forum-section__breadcrumbs">
                            <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                            @if ($forum) <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a> @endif
                            @if ($subforum) <a class="forum-section__breadcrumb" href="/forum/{{$subforum->id}}">{{$subforum->title}}</a> @endif
                            <a class="forum-section__breadcrumb" >{{$topic->title}}</a>
                        </div>
                        <div class="forum-section__title">
                            <div class="forum-section__title__inner">
                                @if ($topic->is_closed)
                                    <div class="forum__locked">
                                        <i class="fa fa-lock"></i>
                                    </div>
                                @endif
                                {{$topic->title}}
                                <div class="forum-section__title__actions">
                                    @include('blocks/forum_topic_panel', ['topic' => $topic])
                                </div>
                            </div>
                            <div class="forum-section__title__buttons">
                                <form action="/forum/{{$topic->forum_id}}-{{$topic->id}}-1" method="GET" class="forum-section__search">
                                    <input placeholder="Поиск по теме"  class="input" name="s" value="{{$search}}">
                                    <button type="submit" class="button"><i class="fa fa-search"></i>Искать</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($show_pager)
            <div class="forum-section__pager-container">

                <div class="forum-section__pager">
                    {{$paginator->links()}}
                </div>
            </div>
            @endif

            @if ($topic->questionnaire_data)
                <div class="questionnaire__container">
                @include('blocks/questionnaire', ['questionnaire' => $topic->questionnaire_data, 'show_results' => $show_results])
                </div>
            @endif
            <div class="forum-section__messages">
                @if ($search != '' && count($messages) == 0)
                <div class="forum-section__messages__nothing-found">
                    По запросу <strong>"{{$search}}"</strong> ничего не найдено
                </div>
                @endif
                @if ($fixed_message)
                    @include('blocks/forum_message', ['fixed' => true, 'message' => $fixed_message])
                @endif
                @foreach ($messages as $message)
                    @include('blocks/forum_message', ['fixed' => false, 'message' => $message, 'highlight' => $search])
                @endforeach
            </div>
            <div class="forum-section__bottom">
                @if (count($messages) > 0 || $fixed_message)
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
                    @endif
                    <form action="/forum/{{$topic->forum_id}}-{{$topic->id}}-1" method="GET" class="forum-section__search">
                        <input placeholder="Поиск по теме" class="input" name="s" value="{{$search}}">
                        <button type="submit" class="button"><i class="fa fa-search"></i>Искать</button>
                    </form>
                </div>
                @endif

                @if (!$topic->is_closed && (\App\Helpers\PermissionsHelper::allows("frreply") || \App\Helpers\PermissionsHelper::allows("frcloset")))
                    <div class="forum-section__form">
                        @include('blocks/forum_form', ['topic_id' => $topic->id])
                    </div>
                @endif
            </div>

        </div>
    </div>
    @include('blocks/change_reputation_modal')
@endsection
