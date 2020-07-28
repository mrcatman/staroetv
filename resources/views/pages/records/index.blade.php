@extends('layouts.default')
@section('page-title')
    {{$params['is_radio'] ? "Архив старых радиозаписей" : "Архив старых телезаписей"}}
@endsection
@section('content')
    <div class="inner-page channels-list-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$params['is_radio'] ? "Архив старых радиозаписей" : "Архив старых телезаписей"}}</div>
            <div class="inner-page__header__right">
                <div class="buttons-row">
                    @if (\App\Helpers\PermissionsHelper::allows('viadd'))
                        @if ($params['is_radio'])
                            <a class="button" href="/radio/add">Добавить радиозапись</a>
                        @else
                            <a class="button" href="/video/add">Добавить видео</a>
                        @endif
                    @endif
                    @if (\App\Helpers\PermissionsHelper::allows('channelsown'))
                        @if ($params['is_radio'])
                            <a class="button" href="/channels/add?is_radio=1">Добавить радиостанцию</a>
                        @else
                            <a class="button" href="/channels/add?is_radio=0">Добавить канал</a>
                        @endif
                    @endif
                </div>

            </div>
        </div>

        <div class="inner-page__content inner-page__content--no-padding">
            <div class="row row--stretch">
                <div class="col col--3">
                    <div class="box">
                        @if ($params['is_radio'])
                          <div class="warning-alert">Большая часть материалов была утеряна за годы неактивности радиоархива, поэтому мы особенно приветствуем любую помощь с заполнением раздела.</div>
                        @endif
                        @include('blocks/records_search', ['is_radio' => $params['is_radio']])
                        <div class="box__inner">
                            <div class="channels-list-page__tabs">
                                <div class="tabs" data-id="channels">
                                    <a class="tab tab--active" data-content="federal">Федеральные</a>
                                    <a class="tab" data-content="regional">Региональные</a>
                                    <a class="tab" data-content="abroad">Зарубежные</a>
                                    <a class="tab" data-content="other">Другие</a>
                                </div>

                                @if (!$params['is_radio'])<a class="button channels-list-page__button--other"  href="/video/other" >Прочее</a>@endif

                            </div>
                            <div class="tab-content" data-id="channels" data-tab="federal">
                                <div class="channels-list">
                                    @foreach($federal as $channel)
                                        @include('blocks/channel_small', ['channel' => $channel])
                                    @endforeach
                                </div>
                            </div>
                            <div style="display: none" class="tab-content" data-id="channels" data-tab="regional">
                                <regional-channels-list :data='@json($regional)'></regional-channels-list>
                            </div>
                            <div style="display: none" class="tab-content" data-id="channels" data-tab="abroad">
                                <regional-channels-list :data='@json($abroad)'></regional-channels-list>
                            </div>
                            <div style="display: none" class="tab-content" data-id="channels" data-tab="other">
                                <div class="channels-list">
                                    @foreach($other as $channel)
                                        @include('blocks/channel_small', ['channel' => $channel])
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (!$params['is_radio'])
                    <div class="box">
                        <a href="/events" class="box__heading">
                            <div class="box__heading__inner">
                                Подборки записей
                            </div>
                        </a>
                        <div class="box__inner">
                            @foreach($events as $event)
                                @include('blocks/event', ['big' => true, 'event' => $event])
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="box box--dark">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                Последние записи
                            </div>
                        </div>
                        <div class="box__inner">
                            @if(!$params['is_radio'])
                            <div class="records-list  records-list--thumbs">
                                @foreach($last_records as $record)
                                    @include('blocks/record', ['record' => $record])
                                @endforeach
                            </div>
                            @else
                                <div class="records-list">
                                    @foreach($last_records as $record)
                                        @include('blocks/radio_recording', ['record' => $record])
                                    @endforeach
                                </div>
                            @endif
                            <div class="records-list__pager-container">
                                {{$last_records->links()}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="box">
                        <div class="box__inner">
                           @include ('blocks/records_material_categories', ['is_radio' => $params['is_radio']])
                        </div>
                    </div>
                    @include('blocks/banner')
                    @include('blocks/generic_sidebar', ['is_radio' => $params['is_radio']])
                </div>
            </div>
        </div>
    </div>

@endsection
