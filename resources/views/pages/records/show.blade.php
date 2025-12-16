@extends('layouts.default')
@section('page-title')
    {{$record->title_without_tags}}
@endsection
@section('head')
    <meta property='og:type' content="video.movie"/>
    <meta property="og:title" content="{{$record->title_without_tags}}"/>
    <meta property="og:image" content="https://staroetv.su/{{$record->cover}}"/>
    @if ($record->use_own_player)
        <meta property="og:video" content="https://staroetv.su{{ $record->source_path }}">
    @endif
@endsection
@section('content')

    <div class="record-page">
        <div class="box">
            <div class="box__breadcrumbs">
                <a class="breadcrumbs__item" href="{{typed_route('records.[RECORD].index', $record->is_radio)}}">Архив</a>
                @if ($record->is_advertising)
                    <a class="breadcrumbs__item"
                       href="{{typed_route('records.[RECORD].commercials', $record->is_radio)}}">Реклама</a>
                    @if ($record->advertising_brand != '')
                        <a class="breadcrumbs__item"
                           href="{{route('records.'.$record->route_prefix.'.commercials-search', ['id' => $record->id])}}">{{$record->advertising_brand}}</a>
                    @endif
                @endif
                @if (!$record->channel && !$record->is_advertising)
                    <a class="breadcrumbs__item" href="{{typed_route('records.[RECORD].other', $record->is_radio)}}">Прочее</a>
                @endif
                @if ($record->channel && !$record->is_advertising)
                    <a class="breadcrumbs__item" href="{{$record->channel->full_url}}">{{$record->channel_name}}</a>
                @endif
                @if ($record->channel && ($record->is_interprogram || $record->interprogram_package_id) && !$record->program)
                    <a class="breadcrumbs__item" href="{{$record->channel->full_url}}#interprogram">Оформление</a>
                    @if ($record->interprogramPackage)
                        <a class="breadcrumbs__item" href="{{$record->interprogramPackage->full_url}}">{{$record->interprogramPackage->full_name}}</a>
                    @else
                        <a class="breadcrumbs__item" href="{{typed_route('design.[CHANNEL].show', $record->is_radio, [$record->channel->url ?? $record->channel->id, 'other'])}}">Прочее</a>
                    @endif
                @endif
                @if ($record->program)
                    <a class="breadcrumbs__item"
                       href="{{$record->program->full_url}}@if($changed_name)?from={{$record->channel_id}}@endif">{{$record->program->name}}</a>
                @endif
            </div>
            <div class="box__heading">
                <div class="box__heading__inner" id="record_title">
                    {{$record->title_without_tags}}
                </div>
                <div class="box__heading__right">
                    @if ($record->can_edit || \App\Helpers\PermissionsHelper::allows('contentapprove'))
                        <span class="button button--light button--dropdown">
                        <span class="button--dropdown__text">Действия</span>
                        <span class="button--dropdown__icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                        <div class="button--dropdown__list">
                            @if (\App\Helpers\PermissionsHelper::allows('contentapprove'))
                                <a class="button--dropdown__list__item" data-approve="records"
                                   data-approve-id="{{$record->id}}">{{$record->pending ? "Одобрить" : "Скрыть"}}</a>
                            @endif
                            @if ($record->can_edit)
                                <a class="button--dropdown__list__item"
                                   href="{{route('records.'.$record->route_prefix.'.edit', $record->id)}}">Редактировать</a>
                                <a class="button--dropdown__list__item" href="{{route('cut.start', $record->id)}}">Обрезка</a>
                                @if (!$record->use_own_player)
                                    <a class="button--dropdown__list__item"
                                       data-confirm-form-input-value="{{$record->id}}"
                                       data-confirm-form-text="Вы уверены, что хотите загрузить эту запись в хранилище сайта из внешнего источника?"
                                       data-confirm-form-url="{{route('records.download', $record->id)}}">Загрузить на сайт</a>
                                    <a class="button--dropdown__list__item" data-show-modal="#update_telegram_id">Указать Telegram ID видео</a>
                                @endif
                                @if (!$record->is_radio)
                                    <a class="button--dropdown__list__item" data-show-modal="#update_preview">Обновить превью</a>
                                @endif
                                <a class="button--dropdown__list__item"
                                   data-confirm-form-input-value="{{$record->id}}"
                                   data-confirm-form-text="Вы уверены, что хотите удалить эту запись?"
                                   data-confirm-form-url="{{route('records.delete')}}">Удалить</a>
                            @endif
                        </div>
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="row row--stretch record-page__content">
            <div class="col col--3">
                <div class="inner-page__content">
                    <div class="record-page__player">
                        @include('blocks.records.player')
                    </div>

                    <div class="box">
                        <div class="box__inner">
                            <div class="record-page__bottom">
                                @include('blocks.records.info')
                                @include('blocks.global.share')
                            </div>

                            @if($record->description != '')
                                <div class="record-page__description">
                                    {!! str_replace(PHP_EOL, "<br>", $record->description_with_timecodes) !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @include('blocks.comments.list', ['class' => 'record-page__comments', 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => \App\Constants\MaterialTypes::TYPE_RECORDS, 'material_id' => $record->ucoz_id]])
            </div>
            <div class="col col--sidebar col--1-5 record-page__related-container">
                @if ($playlist)
                    <div data-current-id="{{$record->id}}" class="box box--dark playlist">
                        <div class="box__heading box__heading--small">
                            <div class="box__heading__inner">
                                Плейлист
                            </div>
                        </div>
                        <div class="box__inner playlist__items">
                            @foreach ($playlist as $item)
                                @if ($item['is_annotation'])
                                    <div class="playlist__annotation">
                                        <div class="playlist__annotation__title">{{$item['data']->title}}</div>
                                        <div class="playlist__annotationn__text">{{$item['data']->text}}</div>
                                    </div>
                                @else
                                    <div class="playlist__item" data-id="{{$item['data']->id}}">
                                        @include($record->is_radio ? 'blocks.records.radio-item' : 'blocks.records.item', ['record' => $item['data']])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    @if ($related_interprogram_packages)
                    @include('blocks.design.related', ['related' => $related_interprogram_packages])
                    @endif
                @endif
                @foreach($related as $related_item)

                    @if (count ($related_item['items']) > 0)
                        <div class="box">

                            <{{$related_item['url'] ? 'a' : 'div'}}
                                @if ($related_item['url']) href="{{$related_item['url']}}" @endif
                            class="box__heading box__heading--small">
                            <div class="box__heading__inner">
                                <span>{{$related_item['heading']}}
                                    @if ($related_item['entity_name'])
                                        <span class="box__heading__count">{{$related_item['entity_name']}}</span>
                                    @endif
                                </span>
                            </div>
                        </{{$related_item['url'] ? 'a' : 'div'}}>
                        <div class="box__inner">
                            <div class="records-list">
                                @foreach ($related_item['items'] as $item)
                                    @include($record->is_radio ? 'blocks.records.radio-item' : 'blocks.records.item', ['record' => $item])
                                @endforeach

                            </div>
                        </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @if ($record->can_edit)
        <div id="update_preview" data-title="Обновить превью" style="display:none">
            <form action="/records/screenshot" class="form modal-window__form" data-reset="1" data-auto-close-modal="1">
                <input type="hidden" name="record_id" value="{{$record->id}}"/>
                @if ($record->use_own_player)
                    <div class="input-container input-container--vertical">
                        <label class="input-container__label">Время, с которого брать кадр (в секундах,
                            опционально)</label>
                        <div class="input-container__inner">
                            <input class="input" name="seconds"/>
                        </div>
                    </div>
                @else
                    <br>
                    Превью будет обновлено из источника: <a target="_blank" href="{{$record->original_url}}">{{$record->original_url}}</a>
                    <br> <br> <br>
                @endif
                <div class="form__bottom">
                    <button class="button button--light">Ок</button>
                    <div class="response response--light"></div>
                </div>
            </form>
        </div>
        <div id="update_telegram_id" data-title="Указать Telegram ID видео" style="display:none">
            <form action="{{route('records.set-telegram-id')}}" class="form modal-window__form" data-reset="1"
                  data-auto-close-modal="1">
                <input type="hidden" name="record_id" value="{{$record->id}}"/>
                <div class="input-container input-container--vertical">
                    <label class="input-container__label">Ссылка в формате CHANNEL_HANDLE/POST_ID</label>
                    <div class="input-container__inner">
                        <input class="input" name="telegram_id" value="{{$record->telegram_id}}"/>
                    </div>
                </div>
                <div class="form__bottom">
                    <button class="button button--light">Ок</button>
                    <div class="response response--light"></div>
                </div>
            </form>
        </div>
    @endif
@endsection
