@extends('layouts.default')
@section('page-title')
{{$record->title_without_tags}}
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
                @else
                    <a class="breadcrumbs__item" href="{{$record->channel->full_url}}/graphics/other">Прочее</a>
                @endif
              @endif
            @if ($record->program)
            <a class="breadcrumbs__item" href="{{$record->program->full_url}}">{{$record->program->name}}</a>
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
                            @elseif (!$record->is_radio)
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
                               <div class="icon-blocks">
                                    @if (!$record->is_advertising && $record->channel)
                                        <a href="{{$record->channel->full_url}}" class="icon-block">
                                            <div class="icon-block__picture"  style="background-image: url({{$record->getChannelLogo()}})"></div>
                                            <span class="icon-block__text">{{$record->getChannelName()}}</span>
                                        </a>
                                    @endif
                                    @if ($record->user) <a href="{{$record->user->url}}" @else <span @endif class="icon-block">
                                        <i class="fa fa-user"></i>
                                        <span class="icon-block__text">{{$record->user ? $record->user->username : $record->author_username}}</span>
                                    @if ($record->user) </a> @else </span> @endif
                                    <span class="icon-block">
                                        @if ($record->is_radio)
                                            <i class="fa fa-headphones-alt"></i>
                                        @else
                                            <i class="fa fa-eye"></i>

                                        @endif
                                        <span class="icon-block__text">{{$record->views}}</span>
                                    </span>
                                    <span class="icon-block">
                                        <i class="fa fa-clock"></i>
                                        <span class="icon-block__text">{{$record->created_at}}</span>
                                    </span>
                                        @include('blocks/share')
                                </div>
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
                </div>
            </div>
        </div>
    </div>
    @if ($record->can_edit)
        <div id="update_preview" data-title="Обновить превью" style="display:none">
            <form action="/records/screenshot" class="form modal-window__form" data-reset="1" data-auto-close-modal="1">
                <input type="hidden" name="record_id" value="{{$record->id}}"/>
                <div class="input-container input-container--vertical">
                    <label class="input-container__label">Время, с которого брать кадр (в секундах, опционально)</label>
                    <div class="input-container__inner">
                        <input class="input" name="seconds" />
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
