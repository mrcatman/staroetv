@extends('layouts.default')
@section('content')
    <div class="inner-page event-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                 <div class="inner-page__header">
                    <div class="inner-page__header__title">{{$event->title}}</div>
                     @if ($event->can_edit || \App\Helpers\PermissionsHelper::allows('historyapprove'))
                         <span class="button button--light button--dropdown" >
                            <span class="button--dropdown__text">Действия</span>
                            <span class="button--dropdown__icon">
                                <i class="fa fa-chevron-down"></i>
                            </span>
                            <div class="button--dropdown__list">
                                @if ($event->can_edit)
                                <a class="button--dropdown__list__item" href="/events/{{$event->id}}/edit">Редактировать</a>
                                @endif
                                @if (\App\Helpers\PermissionsHelper::allows('historyapprove'))
                                    <a class="button--dropdown__list__item" data-approve="events" data-approve-id="{{$event->id}}">{{$event->pending ? "Одобрить" : "Скрыть"}}</a>
                                @endif
                                @if ($event->can_edit)
                                    <a class="button--dropdown__list__item" data-confirm-form-input-name="event_id" data-confirm-form-input-value="{{$event->id}}" data-confirm-form-text="Вы уверены, что хотите удалить подборку?" data-confirm-form-url="/events/delete">Удалить</a>
                                @endif
                            </div>
                        </span>
                     @endif
                </div>
                <div class="inner-page__content">
                    @if ( $event->description != '')
                    <div class="inner-page__text-block event-page__content">
                        {!! $event->description !!}
                    </div>
                    @endif
                </div>
                @foreach ($event->blocks as $block)
                <div class="box box--dark event-page__block">
                    <div class="box__inner">
                        <div class="event-page__block-content"> {!! $block->description !!}</div>
                        <div class="event-page__records @if (count($block->records) > 1) event-page__records--multiple @endif">
                            @foreach($block->records as $record)
                                <div class="event-page__player-container__outer">
                                <div class="event-page__player-container">
                                    @include('blocks/player', ['record' => $record])
                                </div>
                                @if ($record->block_description)
                                <div class="event-page__player-container__description">{{$record->block_description}}</div>
                                @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="box">
                    <div class="box__inner">
                        @include('blocks/share')
                    </div>
                </div>
                <div class="row row--align-start">
                    @include('blocks/comments', ['class' => 'event-page__comments', 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => \App\HistoryEvent::TYPE_HISTORY_EVENT, 'material_id' => $event->id]])
                </div>
            </div>
            <div class="col col--sidebar">
                @include('blocks/generic_sidebar', [ 'is_radio' => false])
            </div>
        </div>

    </div>
@endsection
