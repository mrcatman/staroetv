@extends('layouts.default')
@section('content')
    <div class="forum-page">
        <div class="forum-section">
            <div class="forum-section__breadcrumbs">
                <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                @if ($parent_forum) <a class="forum-section__breadcrumb" href="/forum/{{$parent_forum->id}}">{{$parent_forum->title}}</a> @endif
                <a class="forum-section__breadcrumb" >{{$forum->title}}</a>
            </div>
            <div class="forum-section__title">{{$forum->title}}</div>
            <div class="forum-section__children">
                @if (count($forum->fixed_topics) > 0)
                <div class="forum-section__subsection">
                    <div class="forum-section__subsection__title">Важные темы</div>
                    <div class="forum-section__subsection__children">
                        @foreach ($forum->fixed_topics as $topic)
                            @include('blocks/forum_topic', ['topic' => $topic])
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="forum-section__subsection">
                    <div class="forum-section__subsection__title">Темы форума</div>
                    <div class="forum-section__subsection__children">
                        @foreach ($forum->not_fixed_topics as $topic)
                             @include('blocks/forum_topic', ['topic' => $topic])
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
