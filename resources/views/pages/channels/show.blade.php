@extends('layouts.default')
@section('page-title')
    {{$channel->name}}
@endsection
@section('content')
    <div class="col">

        <div class="box">
            <div class="box__breadcrumbs">
                <div class="breadcrumbs">
                    <a class="breadcrumbs__item"
                       href="{{typed_route('records.[RECORD].index', $channel->is_radio)}}">Архив</a>
                    <a class="breadcrumbs__item breadcrumbs__item--current">{{$channel->name}}</a>
                </div>
            </div>
            <div class="box__heading">
                <h1 class="box__heading__inner">
                    {{$channel->all_names_with_main}}
                </h1>
                <div class="box__heading__right">
                    @if ($channel->can_edit || \App\Helpers\PermissionsHelper::allows('contentapprove'))
                        <span class="button button--light button--dropdown">
                                    <span class="button--dropdown__text">Действия</span>
                                    <span class="button--dropdown__icon">
                                        <i class="fa fa-chevron-down"></i>
                                    </span>
                                    <span class="menu button--dropdown__list">
                                        @if ($channel->can_edit)
                                            <a class="menu__item button--dropdown__list__item"
                                               href="{{typed_route('[CHANNEL].edit', $channel->is_radio, $channel->id)}}">Редактировать</a>
                                        @endif
                                        @if (\App\Helpers\PermissionsHelper::allows('contentapprove'))
                                            <a class="menu__item button--dropdown__list__item" data-approve="channels"
                                               data-approve-id="{{$channel->id}}">{{$channel->pending ? "Одобрить" : "Скрыть"}}</a>
                                        @endif
                                        @if ($channel->can_edit)
                                            <a class="menu__item button--dropdown__list__item"
                                               data-confirm-form-input-value="{{$channel->id}}"
                                               data-confirm-form-text="Вы уверены, что хотите удалить канал?"
                                               data-confirm-form-url="{{typed_route('[CHANNEL].delete', $channel->is_radio)}}">Удалить</a>
                                        @endif
                                    </span>
                                </span>
                    @endif
                </div>
            </div>
            <div class="box__inner">
                <div class="channel-page__top">
                    @if (count($channel->names_with_logos) > 0 || $channel->logo)
                        <div class="channel-page__logos">
                            @if (count($channel->names_with_logos) > 0)
                                <div class="channel-page__selected-logo">
                                    <div class="channel-page__selected-logo__picture__container">
                                        <div class="channel-page__selected-logo__picture"
                                             style="background-image: url({{$channel->names[0]->logo && $channel->names[0]->logo->url ? $channel->names[0]->logo->url : ''}})"></div>
                                        <!--
                                    <div class="channel-page__selected-logo__picture channel-page__selected-logo__picture--shadow"  style="background-image: url({{$channel->names[0]->logo && $channel->names[0]->logo->url ? $channel->names[0]->logo->url : ''}})"></div>
                                    -->
                                    </div>
                                    <!--
                                <div class="channel-page__selected-logo__name">{{$channel->names[0]->name}} </div>
                                -->
                                    <div
                                        class="channel-page__selected-logo__years">{{$channel->names[0]->years_range}} </div>
                                    <div
                                        class="channel-page__selected-logo__description">{{$channel->names[0]->comment}}</div>
                                </div>
                                <div class="channel-page__logos__list">
                                    <div class="channel-page__logos__list__inner">
                                        @foreach($channel->names as $index => $name)
                                            @if ($name->logo)
                                                <a class="channel-page__logos__list__item @if ($index == 0) channel-page__logos__list__item--selected @endif"
                                                   data-info="{{$name}}"
                                                   style="background-image: url({{$name->logo && $name->logo->url ? $name->logo->url : ''}})"></a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @elseif ($channel->logo)
                                <div class="channel-page__selected-logo">
                                    <div class="channel-page__selected-logo__picture__container">
                                        <div class="channel-page__selected-logo__picture"
                                             style="background-image: url({{$channel->logo->url}})"></div>
                                        <!--
                                        <div class="channel-page__selected-logo__picture channel-page__selected-logo__picture--shadow" style="background-image: url({{$channel->logo->url}})"></div>
                                   -->
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="channel-page__description">
                        @if ($channel->description != "")
                            <div class="text-content">{!! $channel->description !!}</div>
                        @else
                            <div class="channel-page__no-description">
                                Описание {{$channel->is_radio ? 'радиостанции' : 'канала'}} еще не заполнено
                            </div>
                        @endif

                        @if ($channel->is_regional || $channel->is_abroad)
                            <div class="channel-page__description__params">
                                <!--
                                    @if (count($channel->unique_names) > 0)
                                    <div class="page__description__param">Также известен как:
                                        <strong>{{$channel->unique_names_list}}</strong></div>



                                @endif
                                -->
                                @if ($channel->is_regional)
                                    <div class="page__description__param">Город/регион:
                                        <strong>{{$channel->city}}</strong></div>
                                @endif
                                @if ($channel->is_abroad)
                                    <div class="page__description__param">Страна:
                                        <strong>{{$channel->country}}</strong></div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @php($programs_edit = \App\Helpers\PermissionsHelper::allows('programs'))
        @php($programs_edit_own = \App\Helpers\PermissionsHelper::allows('programsown'))

        <div class="box">
            <div class="box__heading">
                <div class="box__heading__inner">Программы ({{$channel->name}})</div>
                <div class="box__heading__right">
                    <div class="buttons-row">
                        @if ($programs_edit_own)
                            <a href="{{route('programs.add', ['channel_id' => $channel->id])}}" class="button">
                                <i class="fa fa-edit"></i>
                                Добавить
                            </a>
                        @endif
                        @if ($programs_edit)
                            <a href="{{typed_route('[CHANNEL].programs.edit-list', $channel->is_radio, $channel->url ?? $channel->id)}}"
                               class="button">
                                <i class="fa fa-list"></i>
                                Редактировать список
                            </a>
                        @endif
                    </div>

                </div>
            </div>
            <div class="box__inner">
                @if(count($programs['genres']) > 0)
                    <div class="categories-list">
                        @if ($programs['show_genres'])
                        @foreach($programs['genres'] as $index => $genre)
                            <a data-selector=".category" data-toggle-class="category--active"
                               data-show-block-selector=".programs-list__container"
                               data-show-block-id="{{$genre->id}}"
                               class="category @if ($index == 0) category--active @endif">{{$genre->name}}</a>
                        @endforeach
                        @endif
                    </div>
                @else
                    @include('blocks.global.no-records', ['is_radio' => $channel->is_radio])
                @endif
                <div class="channel-page__programs">

                    @foreach($programs['genres'] as $index => $genre)
                        <div class="programs-list__container" data-block-id="{{$genre->id}}"
                             @if ($index != 0) style="display: none" @endif>
                            <div
                                class="programs-list @if ($programs['show_load_more_button']) programs-list--with-show-more @endif @if ($channel->is_radio) programs-list--radio @endif">
                                @foreach ($genre->programs as $program)
                                    @include('blocks.programs.item', ['url' => $program->full_url.'?from='.$channel->id, 'is_radio' => $channel->is_radio])
                                @endforeach
                            </div>
                            @if ($index === 0 && $programs['show_load_more_button'])
                            <div class="programs-list__show-more">
                                <a data-channel-id="{{$channel->id}}" data-limit="25" class="button">Показать ещё</a>
                            </div>
                            @endif
                        </div>

                    @endforeach
                </div>
            </div>
        </div>
        @if (count($global_programs) > 0)
            <div class="box">
                <div class="box__inner">
                    <div class="programs-list @if ($channel->is_radio) programs-list--radio @endif">
                        @foreach ($global_programs as $program)
                            @include('blocks.programs.item', ['url' => $program->full_url.'?from='.$channel->id, 'is_radio' => $channel->is_radio])
                        @endforeach
                    </div>
                </div>

            </div>
        @endif


        @php($can_edit_interprogram = \App\Helpers\PermissionsHelper::allows('additionalown'))
        @if ($channel->is_radio)
            @include('blocks.records.list', ['hide_if_zero' => true, 'conditions' => $records_conditions_interprogram, 'block_title' => 'Заставки, отбивки, джинглы ('.$channel->name.')'])
        @else
            @if (count($interprogram_packages) > 0 || $can_edit_interprogram)
                <div class="box" id="interprogram">
                    <div class="box__heading">
                        <div class="box__heading__inner">Оформление канала ({{$channel->name}})</div>
                        @if ($can_edit_interprogram)
                            <div class="box__heading__right">
                                <a href="{{route('design.channels.add', $channel->url ?? $channel->id)}}"
                                   class="button button--light">Добавить</a>
                            </div>
                        @endif
                    </div>

                    @if (count($interprogram_packages) > 0)
                        <div class="box__inner">
                            <div class="interprogram-packages-list">
                                @foreach($interprogram_packages as $package)
                                    @include('blocks.design.package', ['package' => $package])
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endif
        <div class="row">
            @include('blocks.records.list', ['conditions' => $records_conditions])
        </div>
        @if (count($articles) > 0)
            <div class="box">
                <a href="{{route('articles.index', ['channel' => $channel->url ?? $channel->id ])}}" class="box__heading">
                    <div class="box__heading__inner">
                        Статьи&nbsp;<span class="box__heading__count">{{$articles['count']}}</span>
                    </div>
                </a>
                <div class="box__inner">
                    <div class="news-blocks-list">
                        @foreach ($articles['list'] as $news_item)
                            @include('blocks.articles.news', ['class' => 'news-block--card news-block--for-channel', 'show_cover' => true, 'news_item' => $news_item])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <div class="row row--align-start">
            @include('blocks.comments.list', ['class' => 'channel-page__comments', 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => \App\Constants\MaterialTypes::TYPE_CHANNELS, 'material_id' => $channel->id]])
        </div>
    </div>
    </div>
@endsection
