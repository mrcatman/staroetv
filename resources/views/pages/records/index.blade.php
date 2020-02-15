@extends('layouts.default')
@section('content')
    <div class="inner-page channels-list-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$data['is_radio'] ? "Архив старых радиозаписей" : "Архив старых видеозаписей"}}</div>
            <div class="inner-page__header__right">
                @if (\App\Helpers\PermissionsHelper::allows('viadd'))
                    @if ($data['is_radio'])
                        <a class="button button--light" href="/radio-recordings/add">Добавить радиозапись</a>
                    @else
                        <a class="button button--light" href="/video/add">Добавить видео</a>
                    @endif
                @endif
                @if (\App\Helpers\PermissionsHelper::allows('channelsown'))
                    @if ($data['is_radio'])
                    <a class="button button--light" href="/channels/add?is_radio=1">Добавить радиостанцию</a>
                    @else
                    <a class="button button--light" href="/channels/add?is_radio=0">Добавить канал</a>
                    @endif
                @endif
            </div>
        </div>
        <div class="inner-page__content inner-page__content--no-padding">
            @include('blocks/records_search', ['is_radio' => $data['is_radio']])
            <div class="channels-list-page__tabs">
                <div class="tabs" data-id="channels">
                    <a class="tab tab--active" data-content="federal">Федеральные</a>
                    <a class="tab" data-content="regional">Региональные</a>
                    <a class="tab" data-content="abroad">Зарубежные</a>
                    <a class="tab" data-content="other">Другие</a>
                </div>
                <a class="button button--light channels-list-page__button--advertising" @if ($data['is_radio']) href="/radio/advertising" @else href="/video/advertising" @endif>Рекламные ролики</a>
            </div>
            <div class="tab-content" data-id="channels" data-tab="federal">
                <div class="channels-list">
                    @foreach($federal as $channel)
                        @include('blocks/channel_small', ['channel' => $channel])
                    @endforeach
                </div>
            </div>
            <div style="display: none" class="tab-content" data-id="channels" data-tab="regional">
                <div class="top-list">
                    <a class="top-list__item top-list__item--all">Все</a>
                    @foreach ($cities as $city => $count)
                        <a class="top-list__item" data-city="{{$city}}">
                            <span class="top-list__item__name">{{$city}}</span>
                            <span class="top-list__item__count">{{$count}}</span>
                        </a>
                    @endforeach
                </div>
                <div class="channels-list">
                    @foreach($regional as $channel)
                        @include('blocks/channel_small', ['channel' => $channel])
                    @endforeach
                </div>
            </div>
            <div style="display: none" class="tab-content" data-id="channels" data-tab="abroad">
                <div class="channels-list">
                    @foreach($abroad as $channel)
                        @include('blocks/channel_small', ['channel' => $channel])
                    @endforeach
                </div>
            </div>
            <div style="display: none" class="tab-content" data-id="channels" data-tab="other">
                <div class="channels-list">
                    @foreach($other as $channel)
                        @include('blocks/channel_small', ['channel' => $channel])
                    @endforeach
                </div>
            </div>

        </div>
        <div class="row">
            <div class="box">
                <div class="box__heading">
                    <div class="box__heading__inner">
                        Последние записи
                    </div>
                </div>
                <div class="box__inner">
                    <div class="records-list records-list--thumbs">
                        @foreach($last_records as $record)
                            @include('blocks/record', ['record' => $record])
                        @endforeach
                    </div>
                    <div class="records-list__pager-container">
                        {{$last_records->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
