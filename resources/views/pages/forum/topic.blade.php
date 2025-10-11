@extends('layouts.default')
@section('page-title')
    {{$topic->title}}
@endsection
@section('content')
    @php($can_reply = !$topic->is_closed && (\App\Helpers\PermissionsHelper::allows("frreply") || \App\Helpers\PermissionsHelper::allows("frcloset")))
    <div class="forum-page">
        <div class="forum-section" data-forum-id="{{$topic->forum_id}}" data-topic-id="{{$topic->id}}">
            <div class="forum__top-panel__outer">
                <div class="forum__top-panel">
                    <div class="forum__top-panel__inner">
                        <div class="forum-section__breadcrumbs">
                            <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                            @if ($forum)
                                <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a>
                            @endif
                            @if ($subforum)
                                <a class="forum-section__breadcrumb"
                                   href="/forum/{{$subforum->id}}">{{$subforum->title}}</a>
                            @endif
                            <a class="forum-section__breadcrumb">{{$topic->title}}</a>
                        </div>
                        <div class="forum-section__title">
                            <div class="forum-section__title__inner">
                                @if ($topic->is_closed)
                                    <div class="forum__param">
                                        <i class="fa fa-lock"></i>
                                    </div>
                                @endif
                                {{$topic->title}}
                                <div class="forum-section__title__actions">
                                    @include('blocks.forum.topic-actions', ['topic' => $topic])
                                </div>
                            </div>
                            <div class="forum-section__title__buttons">
                                <form action="/forum/{{$topic->forum_id}}-{{$topic->id}}-1" method="GET"
                                      class="forum-section__search">
                                    <input placeholder="Поиск по теме" class="input" name="s" value="{{$search}}">
                                    <button type="submit" class="button"><i class="fa fa-search"></i>Искать</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($show_pager)
                <div class="box">
                    <div class="box__pager">

                        <div class="forum-section__pager">
                            {{$paginator->links()}}
                        </div>
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
                    <div class="box">
                        <div class="box__inner">
                            <div class="forum-section__messages__nothing-found">
                                По запросу <strong>"{{$search}}"</strong> ничего не найдено
                            </div>
                        </div>
                    </div>

                @endif
                @if ($fixed_message)
                    @include('blocks.forum.message', ['fixed' => true, 'message' => $fixed_message])
                @endif
                @foreach ($messages as $message)
                    @include('blocks.forum.message', ['fixed' => false, 'message' => $message, 'highlight' => $search])
                @endforeach
            </div>
            <div class="box">
                <div class="box__heading">
                    <div class="forum-section__breadcrumbs forum-section__breadcrumbs--bottom">
                        <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                        @if ($forum)
                            <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a>
                        @endif
                        @if ($subforum)
                            <a class="forum-section__breadcrumb"
                               href="/forum/{{$subforum->id}}">{{$subforum->title}}</a>
                        @endif
                        <a class="forum-section__breadcrumb">{{$topic->title}}</a>
                    </div>
                </div>
                <div class="box__inner">@if (count($messages) > 0 || $fixed_message)@if ($show_pager)<div class="box__pager forum-section__pager-container">
                                {{$paginator->links()}}

                                <form action="/forum/{{$topic->forum_id}}-{{$topic->id}}-1" method="GET"
                                      class="input-container forum-section__search forum-section__search--bottom">

                                    <div class="input-container__inner input-container__inner--with-icon">
                                        <i class="fa fa-search input-container__icon"></i>
                                        <input placeholder="Поиск по теме" class="input" name="s" value="{{$search}}">
                                    </div>

                                    <button type="submit" class="button"><i class="fa fa-search"></i>Искать</button>
                                </form>
                            </div>@endif</div>
                @endif
                @if ($can_reply)
                    <div class="forum-section__form">
                        @include('blocks.forum.bb-editor', ['topic_id' => $topic->id])
                    </div>
                @endif
            </div>
        </div>

    </div>
    </div>
    @include('blocks/change_reputation_modal')
@endsection
