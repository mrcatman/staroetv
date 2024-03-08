@extends('layouts.default')
@section('page-title')
{{$record->title_without_tags}}
@endsection
@section('head')
<meta property='og:type' content="video.movie" />
<meta property="og:title" content="{{$record->title_without_tags}}" />
<meta property="og:image" content="https://staroetv.su/{{$record->cover}}" />
@if ($record->use_own_player)
    <meta property="og:video" content="https://staroetv.su{{ $record->source_path }}">
@endif

@endsection
@section('content')
    <div class="inner-page record-page">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="{{$record->is_radio ? "/radio" : "/video"}}">Архив</a>
            @if ($record->is_advertising)
                <a class="breadcrumbs__item" href="{{$record->is_radio ? "/radio" : "/video"}}/commercials">Реклама</a>
                @if ($record->advertising_brand != '')
                <a class="breadcrumbs__item" href="{{$record->is_radio ? "/radio" : "/video"}}/commercials-search?id={{$record->id}}">{{$record->advertising_brand}}</a>
                @endif
            @endif
            @if (!$record->channel && !$record->is_advertising)
                <a class="breadcrumbs__item" href="{{$record->is_radio ? "/radio" : "/video"}}/other">Прочее</a>
            @endif
            @if ($record->channel && !$record->is_advertising)
            <a class="breadcrumbs__item" href="{{$record->channel->full_url}}">{{$record->getChannelName()}}</a>
            @endif
            @if ($record->channel && ($record->is_interprogram || $record->interprogram_package_id) && !$record->program)
            <a class="breadcrumbs__item" href="{{$record->channel->full_url}}#interprogram">Оформление</a>
                @if ($record->interprogramPackage)
                 <a class="breadcrumbs__item" href="{{$record->interprogramPackage->full_url}}">{{$record->interprogramPackage->full_name}}</a>
                @elseif (!$record->is_radio)
                    <a class="breadcrumbs__item" href="{{$record->channel->full_url}}/graphics/other">Прочее</a>
                @endif
              @endif
            @if ($record->program)
            <a class="breadcrumbs__item" href="{{$record->program->full_url}}@if($changed_name)?from={{$record->channel_id}}@endif">{{$record->program->name}}</a>
            @endif
        </div>
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$record->title_without_tags}}</div>
            @if ($record->can_edit || \App\Helpers\PermissionsHelper::allows('contentapprove'))
                <div class="inner-page__header__right">
                    <span class="button button--light button--dropdown">
                        <span class="button--dropdown__text">Действия</span>
                        <span class="button--dropdown__icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                        <div class="button--dropdown__list">
                            @if (\App\Helpers\PermissionsHelper::allows('contentapprove'))
                                <a class="button--dropdown__list__item" data-approve="records" data-approve-id="{{$record->id}}">{{$record->pending ? "Одобрить" : "Скрыть"}}</a>
                            @endif
                            @if ($record->can_edit)
                            <a class="button--dropdown__list__item" href="{{$record->channel && $record->channel->is_radio ? "/radio" : "/video"}}/{{$record->id}}/edit">Редактировать</a>
                            <a class="button--dropdown__list__item" href="/cut/start/{{$record->id}}">Обрезка</a>
                            @if (!$record->use_own_player)
                            <a class="button--dropdown__list__item"  data-confirm-form-input-name="record_id" data-confirm-form-input-value="{{$record->id}}" data-confirm-form-text="Вы уверены, что хотите загрузить эту запись в хранилище сайта из внешнего источника?" data-confirm-form-url="/records/download">Загрузить на сайт</a>
                            <a class="button--dropdown__list__item" data-show-modal="#update_telegram_id">Указать Telegram ID видео</a>
                            @endif
                            @if (!$record->is_radio)
                                <a class="button--dropdown__list__item" data-show-modal="#update_preview">Обновить превью</a>
                            @endif
                            <a class="button--dropdown__list__item" data-confirm-form-input-name="record_id" data-confirm-form-input-value="{{$record->id}}" data-confirm-form-text="Вы уверены, что хотите удалить эту запись?" data-confirm-form-url="/records/delete">Удалить</a>
                            @endif
                        </div>
                    </span>
                </div>
            @endif
        </div>
        <div class="row row--stretch record-page__content">
            <div class="col col--3">
                <div class="inner-page__content">
                    <div class="record-page__player">
                    @include('blocks/player', ['record' => $record])
                    </div>

                    <div class="box">
                        <div class="box__inner">
                            <div class="record-page__bottom">
                               @include('blocks/record_info', ['record' => $record])
                                @include('blocks/share')
                            </div>

                            <div class="record-page__description">
                                {!! str_replace(PHP_EOL, "<br>", $record->description) !!}
                            </div>
                        </div>
                    </div>
                </div>
                @include('blocks/comments', ['class' => 'record-page__comments', 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => 10, 'material_id' => $record->ucoz_id]])
            </div>
            <div class="col col--2 record-page__related-container">
                @if ($playlist)
                <div data-current-id="{{$record->id}}" class="box box--dark playlist">
                    <div class="box__heading box__heading--small">
                        <div class="box__heading__inner">
                            Плейлист
                        </div>
                    </div>
                    <div class="box__inner">
                        @foreach ($playlist as $item)
                            @if ($item['is_annotation'])
                             <div class="playlist__annotation">
                                 <div class="playlist__annotation__title">{{$item['data']->title}}</div>
                                 <div class="playlist__annotationn__text">{{$item['data']->text}}</div>
                             </div>
                             @else
                             <div class="playlist__item" data-id="{{$item['data']->id}}">
                                @include($record->is_radio ? 'blocks/radio_recording' : 'blocks/record', ['record' => $item['data']])
                             </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @else
                <div class="box">
                @if ($related_program && count ($related_program) > 0)
                    <a href="{{$record->program->full_url}}" class="box__heading box__heading--small">
                        <div class="box__heading__inner">
                            Другие выпуски программы <span class="box__heading__count">{{$record->program->name}}</span>
                        </div>
                    </a>
                    <div class="box__inner">
                        <div class="record-page__related">
                            @foreach ($related_program as $item)
                                @include($record->is_radio ? 'blocks/radio_recording' : 'blocks/record', ['record' => $item])
                            @endforeach

                        </div>
                    </div>
                @endif
                @if ($related_interprogram && count ($related_interprogram) > 0)
                        <a href="{{$record->interprogramPackage->full_url}}" class="box__heading box__heading--small">
                            <div class="box__heading__inner">
                                Еще записи этого оформления
                            </div>
                        </a>
                        <div class="box__inner">
                            <div class="record-page__related">
                                @foreach ($related_interprogram as $item)
                                    @include($record->is_radio ? 'blocks/radio_recording' : 'blocks/record', ['record' => $item])
                                @endforeach
                            </div>
                        </div>
                @endif
                    @if ($related_channel && count($related_channel) > 0)
                    <a href="{{$record->channel->full_url}}" class="box__heading box__heading--small">
                        <div class="box__heading__inner">
                            {{$record->is_radio ? "Еще записи с радиостанции" : "Еще записи с канала"}} <span class="box__heading__count">{{$record->getChannelName()}}</span>
                        </div>
                    </a>
                    <div class="box__inner">
                        <div class="record-page__related">
                             @foreach ($related_channel as $item)
                                @include($record->is_radio ? 'blocks/radio_recording' : 'blocks/record', ['record' => $item])
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if ($related_advertising && count($related_advertising) > 0)
                        <div class="box__heading box__heading--small">
                            <div class="box__heading__inner">
                                Ещё реклама <!--<span class="box__heading__count">{{$record->advertising_brand}}--></span>
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="record-page__related">
                                @foreach ($related_advertising as $item)
                                    @include($record->is_radio ? 'blocks/radio_recording' : 'blocks/record', ['record' => $item])
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($related_other && count($related_other) > 0)
                        <div class="box__heading box__heading--small">
                            <div class="box__heading__inner">
                                Другие записи
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="record-page__related">
                                @foreach ($related_other as $item)
                                    @include($record->is_radio ? 'blocks/radio_recording' : 'blocks/record', ['record' => $item])
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @if ($record->can_edit)
        <div id="update_preview" data-title="Обновить превью" style="display:none">
            <form action="/records/screenshot" class="form modal-window__form" data-reset="1" data-auto-close-modal="1">
                <input type="hidden" name="record_id" value="{{$record->id}}"/>
                @if ($record->use_own_player)
                <div class="input-container input-container--vertical">
                    <label class="input-container__label">Время, с которого брать кадр (в секундах, опционально)</label>
                    <div class="input-container__inner">
                        <input class="input" name="seconds" />
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
            <form action="/records/set-telegram-id" class="form modal-window__form" data-reset="1" data-auto-close-modal="1">
                <input type="hidden" name="record_id" value="{{$record->id}}"/>
                <div class="input-container input-container--vertical">
                    <label class="input-container__label">Ссылка в формате CHANNEL_HANDLE/POST_ID</label>
                    <div class="input-container__inner">
                        <input class="input" name="telegram_id" value="{{$record->telegram_id}}" />
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
