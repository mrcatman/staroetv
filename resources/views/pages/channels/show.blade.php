@extends('layouts.default')
@section('page-title')
    {{$channel->name}}
@endsection
@section('content')
    <div class="inner-page channel-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="breadcrumbs">
                    <a class="breadcrumbs__item" href="/{{$channel->is_radio ? "radio" : "video"}}">Архив</a>
                    <a class="breadcrumbs__item breadcrumbs__item--current">{{$channel->name}}</a>
                </div>
                <div class="box">
                    <div class="inner-page__header">
                        <div class="inner-page__header__title">{{$channel->all_names_with_main}}</div>
                        @if ($channel->can_edit || \App\Helpers\PermissionsHelper::allows('contentapprove'))
                            <span class="button button--light button--dropdown" >
                    <span class="button--dropdown__text">Действия</span>
                    <span class="button--dropdown__icon">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                    <div class="button--dropdown__list">
                        @if ($channel->can_edit)
                            <a class="button--dropdown__list__item" href="/channels/{{$channel->id}}/edit">Редактировать</a>
                        @endif
                        @if (\App\Helpers\PermissionsHelper::allows('contentapprove'))
                            <a class="button--dropdown__list__item" data-approve="channels" data-approve-id="{{$channel->id}}">{{$channel->pending ? "Одобрить" : "Скрыть"}}</a>
                        @endif
                        @if ($channel->can_edit)
                            <a class="button--dropdown__list__item" data-confirm-form-input-name="channel_id" data-confirm-form-input-value="{{$channel->id}}" data-confirm-form-text="Вы уверены, что хотите удалить канал?" data-confirm-form-url="/channels/delete">Удалить</a>
                        @endif
                    </div>
                </span>
                        @endif
                    </div>
                    <div class="inner-page__content">
                        <div class="channel-page__top">
                            @if (count($channel->names_with_logos) > 0 || $channel->logo)
                                <div class="channel-page__logos">
                                    @if (count($channel->names_with_logos) > 0)
                                        <div class="channel-page__selected-logo">
                                            <div class="channel-page__selected-logo__picture__container">
                                                <div class="channel-page__selected-logo__picture" style="background-image: url({{$channel->names[0]->logo && $channel->names[0]->logo->url ? $channel->names[0]->logo->url : ''}})"></div>
                                            <!--
                                    <div class="channel-page__selected-logo__picture channel-page__selected-logo__picture--shadow"  style="background-image: url({{$channel->names[0]->logo && $channel->names[0]->logo->url ? $channel->names[0]->logo->url : ''}})"></div>
                                    -->
                                            </div>
                                        <!--
                                <div class="channel-page__selected-logo__name">{{$channel->names[0]->name}} </div>
                                -->
                                            <div class="channel-page__selected-logo__years">{{$channel->names[0]->years_range}} </div>
                                            <div class="channel-page__selected-logo__description">{{$channel->names[0]->comment}}</div>
                                        </div>
                                        <div class="channel-page__logos__list">
                                            <div class="channel-page__logos__list__inner">
                                                @foreach($channel->names as $index => $name)
                                                    @if ($name->logo)
                                                        <a class="channel-page__logos__list__item @if ($index == 0) channel-page__logos__list__item--selected @endif" data-info="{{$name}}" style="background-image: url({{$name->logo && $name->logo->url ? $name->logo->url : ''}})"></a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif ($channel->logo)
                                        <div class="channel-page__selected-logo">
                                            <div class="channel-page__selected-logo__picture__container">
                                                <div class="channel-page__selected-logo__picture" style="background-image: url({{$channel->logo->url}})"></div>
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
                                    <div class="channel-page__description__text">{!! $channel->description !!}</div>
                                @else
                                    <div class="channel-page__no-description">Описание канала еще не заполнено</div>
                                @endif

                                <div class="channel-page__description__params">
                                    @if (count($channel->unique_names) > 0)
                                        <div class="page__description__param">Также известен как: <strong>{{$channel->unique_names_list}}</strong></div>
                                    @endif
                                    @if ($channel->is_regional)
                                        <div class="page__description__param">Город: <strong>{{$channel->city}}</strong></div> @endif
                                    @if ($channel->is_abroad)
                                        <div class="page__description__param">Страна: <strong>{{$channel->country}}</strong></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @php($programs_edit = \App\Helpers\PermissionsHelper::allows('programs'))
                @php($programs_edit_own = \App\Helpers\PermissionsHelper::allows('programsown'))
                @if (count($programs) > 0 || $programs_edit || $programs_edit_own)
                    <div class="box">
                        <div class="box__heading">
                            <div class="box__heading__title">Программы ({{$channel->name}})</div>
                            <div class="box__heading__right">
                                @if ($programs_edit_own)
                                    <a href="{{$channel->full_url}}/programs/add" class="button button--light">Добавить</a>
                                @endif
                                @if ($programs_edit)
                                    <a href="{{$channel->full_url}}/programs/edit" class="button button--light">Редактировать список</a>
                                @endif
                            </div>
                        </div>
                        @if(count($programs) > 0)
                            <div class="categories-list">
                                @foreach($programs as $index => $genre)
                                    <a data-selector=".category" data-toggle-class="category--active" data-show-block-selector=".programs-list" data-show-block-id="{{$genre->id}}" class="category @if ($index == 0) category--active @endif">{{$genre->name}}</a>
                                @endforeach
                            </div>
                        @endif
                        <div class="box__inner">
                            <div class="channel-page__programs">

                                @foreach($programs as $index => $genre)
                                    <div class="programs-list" data-block-id="{{$genre->id}}" @if ($index != 0) style="display: none" @endif>
                                        @foreach ($genre->programs as $program)
                                            <a href="{{$program->full_url}}" class="program @if ($program->pending) program--pending @endif">
                                                <span class="program__cover" style="background-image: url({{$program->cover_url}})"></span>
                                                <span class="program__name">{{$program->name}}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                @php($can_edit_interprogram = \App\Helpers\PermissionsHelper::allows('additionalown'))
                @if ($channel->is_radio)
                    @include('blocks/records_list', ['conditions' => $records_conditions_interprogram, 'block_title' => 'Заставки, отбивки, джинглы ('.$channel->name.')'])
                @else
                    @if (count($interprogram_packages) > 0 || $can_edit_interprogram)
                        <div class="box" id="interprogram">
                            <div class="box__heading">
                                <div class="box__heading__inner">Оформление канала ({{$channel->name}})</div>
                                @if ($can_edit_interprogram)
                                    <div class="box__heading__right">
                                        <a href="{{$channel->full_url}}/graphics/add" class="button button--light">Добавить</a>
                                    </div>
                                @endif
                            </div>

                            @if (count($interprogram_packages) > 0)
                                <div class="box__inner">
                                    <div class="interprogram-packages-list">
                                        @foreach($interprogram_packages as $package)
                                            <a href="{{$package->full_url}}" class="interprogram-package">
                                                <div class="interprogram-package__cover">
                                                    @if (($package->coverPicture))
                                                        <div class="interprogram-package__cover__picture interprogram-package__cover__picture--big" style="background-image: url({{$package->coverPicture->url}})"></div>
                                                    @else
                                                        @foreach ($package->records->take(4) as $record)
                                                            <div class="interprogram-package__cover__picture" style="background-image: url({{$record->cover}})"></div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <div class="interprogram-package__name">{{$package->name}}</div>
                                                <div class="interprogram-package__years">{{$package->years_range}}</div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @endif
                        </div>
                    @endif
                    <div class="row">
                        @include('blocks/records_list', ['conditions' => $records_conditions])
                    </div>
                    <div class="row row--align-start">
                        @include('blocks/comments', ['class' => 'channel-page__comments', 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => \App\Channel::TYPE_CHANNELS, 'material_id' => $channel->id]])
                    </div>
                </div>
            </div>
            <div class="col col--sidebar">
                @include('blocks/generic_sidebar', ['is_radio' => $channel->is_radio])
            </div>
        </div>
    </div>
</div>
@endsection
