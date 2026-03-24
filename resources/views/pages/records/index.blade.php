@extends('layouts.default', ['vue' => true])
@section('page-title')
    {{$params['is_radio'] ? "Архив старых радиозаписей" : "Архив старых телезаписей"}}
@endsection
@section('content')
    @php($route_prefix = route_prefix_records($params['is_radio']))
    @php($route_prefix_channels = route_prefix_channels($params['is_radio']))

    <div class="col">
        <div class="box">
            <div class="box__heading box__heading--no-border">
                <h1 class="box__heading__inner">
                    {{$params['is_radio'] ? "Архив старых радиозаписей" : "Архив старых телезаписей"}}
                </h1>
                <div class="box__heading__right">
                    @if (\App\Helpers\PermissionsHelper::allows('channelsown') || \App\Helpers\PermissionsHelper::allows('viadd'))
                        <div class="buttons-row">
                            @if (\App\Helpers\PermissionsHelper::allows('channelsown'))
                                <a class="button"
                                   href="{{typed_route('[CHANNEL].add', $params['is_radio'])}}">{{$params['is_radio'] ? 'Добавить радиостанцию' : 'Добавить канал'}}</a>
                            @endif

                            @if (\App\Helpers\PermissionsHelper::allows('viadd'))
                                @if ($params['is_radio'])
                                    <a class="button"
                                       href="{{typed_route('records.[RECORD].add', $params['is_radio'])}}">
                                        <i class="fa fa-file-audio"></i>
                                        Добавить радиозапись
                                    </a>
                                @else
                                    <a class="button"
                                       href="{{typed_route('records.[RECORD].add', $params['is_radio'])}}">
                                        <i class="fa fa-film"></i>
                                        Добавить видео
                                    </a>
                                    <a class="button"
                                       href="{{typed_route('mass-upload.[RECORD]', $params['is_radio'])}}">
                                        <i class="fa fa-upload"></i>
                                        Массовая загрузка
                                    </a>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row row--stretch">
            <div class="col col--2-5">
                <div class="box">
                    <div class="box__inner">
                        <form class="channels-list-page__search" method="GET" action="{{route('records.search')}}">
                            <input type="hidden" name="is_radio" value="{{$params['is_radio'] ? 1 : 0}}"/>
                            <div class="row row--mobile">
                                <div class="col">
                                    <div class="input-container ">
                                        <div class="input-container__inner">
                                            <input class="input" name="search" placeholder="Поиск записей"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col col--auto">
                                    <button type="submit" class="button"><i class="fa fa-search"></i>Найти</button>
                                </div>
                            </div>
                        </form>
                        <div class="horisontal-delimiter"></div>

                        <div class="channels-list-page__tabs">
                            <div class="tabs" data-id="channels">
                                <a class="tab tab--active" data-content="federal">Федеральные</a>
                                <a class="tab" data-content="regional">Местные</a>
                                <a class="tab" data-content="abroad">Зарубежные</a>
                                @if (count($other) > 0)
                                    <a class="tab" data-content="other">Другие</a>
                                @endif
                            </div>

                        </div>
                        <div class="tab-content" data-id="channels" data-tab="federal">
                            <div class="channels-list">
                                @foreach($federal as $channel)
                                    @include('blocks.channels.item')
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
                                    @include('blocks.channels.item')
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @if (!$params['is_radio'] && count($events) > 0)
                    <div class="box">
                        <a href="{{route('events.index')}}" class="box__heading">
                            <div class="box__heading__inner">
                                Подборки записей
                            </div>
                        </a>
                        <div class="box__inner">
                            @foreach($events as $event)
                                @include('blocks.events.item', ['big' => true, 'event' => $event])
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (count($other_categories) > 0)
                    <div class="box">
                        <div class="box__inner">
                            <div class="programs-list">
                                @foreach ($other_categories as $other_category)
                                    @include('blocks.programs.item', ['program' => $other_category])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @include('blocks.teletext.banner-horizontal')

                <div class="box box--dark" id="latest_records">
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            Последние записи
                        </div>
                    </div>
                    <div class="box__inner">
                        @if(!$params['is_radio'])
                            <div class="records-list records-list--thumbs records-list--thumbs-only-desktop">
                                @foreach($last_records as $record)
                                    @include('blocks.records.item', ['record' => $record])
                                @endforeach
                            </div>
                        @else
                            <div class="records-list">
                                @foreach($last_records as $record)
                                    @include('blocks.records.radio-item', ['record' => $record])
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="box__pager">
                        {{$last_records->links()}}
                    </div>
                </div>
            </div>
            <div class="col col--sidebar">
                <div class="box">
                    <div class="box__inner">
                        @include ('blocks.records.material-categories', ['is_radio' => $params['is_radio']])
                    </div>
                </div>
                @include('blocks.global.digitization')
                @include('blocks.global.generic-sidebar', ['is_radio' => $params['is_radio']])
            </div>
        </div>
    </div>
@endsection
