@extends('layouts.default')
@section('page-title')
    Подборки записей
@endsection
@section('content')
 <div class="inner-page events-list-page">
    <div class="inner-page__header">
        <div class="inner-page__header__title">
            Подборки записей
        </div>
        <div class="inner-page__header__right inner-page__header__right--big">
            <a href="/events/add" class="button">Предложить свою</a>
        </div>
    </div>
    <div class="inner-page__content">
        <div class="row">
            <div class="col">
                <div class="box">
                    <div class="box__inner">
                        @if ($big_event)
                            <div class="events-list-page__top">
                                <a href="{{$big_event->full_url}}" class="events-list-page__big-event" style="background-image:url({{$big_event->coverPicture ? $big_event->coverPicture->url : ''}})">
                                    <div class="events-list-page__big-event__texts">
                                        <div class="events-list-page__big-event__title">{{$big_event->title}}</div>
                                        <div class="events-list-page__big-event__description">{{$big_event->short_description}}</div>
                                    </div>
                                </a>
                                <div class="events-list-page__top__first-events">
                                    @foreach($first_events as $event)
                                        @include('blocks/event', ['big' => true, 'event' => $event])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @foreach($events as $event)
                            @include('blocks/event', ['big' => true, 'event' => $event])
                        @endforeach
                    </div>
                </div>

                <div class="pager-container pager-container--box">
                    {{$events->links()}}
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
