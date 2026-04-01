@extends('layouts.default')
@section('page-title')
    {{$program->name}}
@endsection
@section('content')
    <div class="program-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="box">
                    <div class="box__breadcrumbs">
                        <div class="breadcrumbs">
                            @if ($channel)
                                <a class="breadcrumbs__item"
                                   href="{{typed_route('records.[RECORD].index', $channel->is_radio)}}">Архив</a>
                                <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
                            @endif
                            <a class="breadcrumbs__item breadcrumbs__item--current">{{$program->name}}</a>
                        </div>
                    </div>
                    <div class="box__heading">
                        <h1 class="box__heading__inner">
                            {{$program->name}}
                        </h1>

                        <div class="box__heading__right">
                            @if (!$unknown && ($program->can_edit || \App\Helpers\PermissionsHelper::allows('contentapprove')))
                                <span class="button button--light button--dropdown">
                                    <span class="button--dropdown__text">Действия</span>
                                    <span class="button--dropdown__icon">
                                        <i class="fa fa-chevron-down"></i>
                                    </span>
                                    <div class="menu button--dropdown__list">
                                          @if ($program->can_edit)
                                            <a class="menu__item button--dropdown__list__item" href="{{route('programs.edit', $program)}}">Редактировать</a>
                                        @endif
                                        @if (\App\Helpers\PermissionsHelper::allows('contentapprove'))
                                            <a class="menu__item button--dropdown__list__item" data-approve="programs" data-approve-id="{{$program->id}}">{{$program->pending ? "Одобрить" : "Скрыть"}}</a>
                                        @endif
                                        @if ($program->can_edit)
                                            <a class="menu__item button--dropdown__list__item"
                                               data-confirm-form-input-value="{{$program->id}}"
                                               data-confirm-form-text="Вы уверены, что хотите удалить программу?"
                                               data-confirm-form-url="{{route('programs.delete')}}">Удалить</a>
                                        @endif
                                    </div>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="row">

                            @if(!$unknown && $cover)
                                <img class="program-page__logo" src="{{$cover}}">
                            @endif
                            <div class="program-page__texts">
                                @if (!$unknown && count($program->unique_names) > 0)
                                    <div class="program-page__names">
                                        <strong>Также известна как:</strong>
                                        {{implode(", ", $program->unique_names)}}
                                    </div>
                                @endif
                                @if (!$unknown && $program->channel_id)
                                    <div class="program__channels program-page__channels">
                                        @foreach ($program->channels_history as $program_channel)
                                            <a href="{{$program_channel['url']}}" class="program__channel__name">
                                                @if ($program_channel['logo'])
                                                    <img class="program__channel__logo"
                                                         src="{{$program_channel['logo']}}"/>
                                                @endif
                                                {{$program_channel['name']}}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                        </div>
                        <div class="program-page__description channel-page__description">
                            @if ($program->description != "")
                                <div class="text-content">{!! $program->description !!}</div>
                            @else
                                <div class="channel-page__no-description program-page__no-description">Описание программы еще не заполнено</div>
                            @endif

                        </div>
                    </div>
                </div>

                <div class="row">
                    @include('blocks.records.list', ['conditions' => $records_conditions])
                </div>
                @if (count($program->articles) > 0)
                    <div class="box">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                Статьи
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="news-blocks-list">
                            @foreach ($program->articles as $news_item)
                                @include('blocks.articles.news', ['class' => 'news-block--card news-block--for-program', 'show_cover' => true, 'news_item' => $news_item])
                            @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                @php($can_edit_interprogram = \App\Helpers\PermissionsHelper::allows('additionalown'))
                @if (!$unknown && (count($program->design) > 0 || $can_edit_interprogram))
                    <div class="box interprogram-packages-list-item">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                Оформление программы
                            </div>
                            @if ($can_edit_interprogram)
                                <div class="box__heading__right">
                                    <a href="{{route('design.programs.add', $program)}}"
                                       class="button button--light">Добавить</a>
                                </div>
                            @endif
                        </div>
                        <div class="box__inner">
                            <div class="interprogram-packages-list-item__inner">
                                <div class="interprogram-packages-list-item__videos">
                                    @if (count($program->interprogramPackages) > 0)
                                        <div class="interprogram-packages-list">
                                            @foreach($program->interprogramPackages as $package)
                                                @include('blocks.design.package', ['package' => $package])
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="records-list records-list--thumbs">
                                            @foreach($program->design as $record)
                                                @include('blocks.records.item')
                                            @endforeach
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (!$unknown)
                <div class="row">
                    @include('blocks.comments.list', ['class' => 'program-page__comments', 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => \App\Constants\MaterialTypes::TYPE_PROGRAMS, 'material_id' => $program->id]])
                </div>
                @endif
            </div>
            <div class="col col--sidebar">
                @if (count($related_programs) > 0)
                    <div class="box">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                Похожие передачи
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="records-list">
                                @foreach ($related_programs as $program)
                                    @include('blocks.records.item', ['record' => $program, 'title' => $program->name, 'hide_info' => true])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                @include('blocks.global.generic-sidebar', ['hide_articles' => true, 'is_radio' => $channel ? $channel->is_radio : false])
            </div>
        </div>
    </div>

@endsection
