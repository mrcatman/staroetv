@extends('layouts.default', ['vue' => true])
@section('page-title')
    {{$params['is_radio'] ? "Архив старых радиозаписей" : "Архив старых телезаписей"}}
@endsection
@section('content')
    @php($route_prefix = route_prefix_records($params['is_radio']))
    @php($route_prefix_channels = route_prefix_channels($params['is_radio']))

    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                {{$params['is_radio'] ? "Архив старых радиозаписей" : "Архив старых телезаписей"}}
            </div>
            <div class="box__heading__right">
                @if (\App\Helpers\PermissionsHelper::allows('channelsown') || \App\Helpers\PermissionsHelper::allows('viadd'))
                <div class="buttons-row">
                    @if (\App\Helpers\PermissionsHelper::allows('channelsown'))
                       <a class="button" href="{{typed_route('[CHANNEL].add', $params['is_radio'])}}">{{$params['is_radio'] ? 'Добавить радиостанцию' : 'Добавить канал'}}</a>
                    @endif

                    @if (\App\Helpers\PermissionsHelper::allows('viadd'))
                        @if ($params['is_radio'])
                            <a class="button" href="{{typed_route('records.[RECORD].add', $params['is_radio'])}}">
                                <i class="fa fa-file-audio"></i>
                                Добавить радиозапись
                            </a>
                        @else
                            <a class="button" href="{{typed_route('records.[RECORD].add', $params['is_radio'])}}">
                                <i class="fa fa-film"></i>
                                Добавить видео
                            </a>
                            <a class="button" href="{{route('mass-upload.index')}}">
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
                        @include('blocks/records_search', ['is_radio' => $params['is_radio']])
                        <div class="channels-list-page__tabs">
                            <div class="tabs" data-id="channels">
                                <a class="tab tab--active" data-content="federal">Федеральные</a>
                                <a class="tab" data-content="regional">Местные</a>
                                <a class="tab" data-content="abroad">Зарубежные</a>
                                <a class="tab" data-content="other">Другие</a>
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
                                @include('blocks/event', ['big' => true, 'event' => $event])
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (count($other_categories) > 0)
                    <div class="box">
                        <div class="box__inner">
                            <div class="programs-list">
                                @foreach ($other_categories as $other_category)
                                    @include('blocks.programs.item', ['program' => $other_category, 'url' => typed_route('records.[RECORD].other.category', $params['is_radio'], $other_category->url)])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @include('blocks.teletext.banner-horizontal')

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
                @include('blocks.banner')
                @include('blocks.generic_sidebar', ['is_radio' => $params['is_radio']])
            </div>
        </div>

@endsection
