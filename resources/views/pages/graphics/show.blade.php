@extends('layouts.default')
@section('content')
    <div class="inner-page interprogram-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="breadcrumbs">
                    <a class="breadcrumbs__item" href="/{{$channel->is_radio ? "radio" : "video"}}">Архив</a>
                    <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
                    <a class="breadcrumbs__item" href="{{$channel->full_url}}#interprogram">Оформление</a>
                    <a class="breadcrumbs__item breadcrumbs__item--current">{{$package->full_name}}</a>
                </div>
                <div class="inner-page__header">
                    <div class="inner-page__header__title">Заставки канала {{$channel->all_names_with_main}}</div>
                    <div class="inner-page__header__right">
                        @if ($other)
                            <a href="{{$base_link}}?hide_commercials={{$hide_commercials ? 0 : 1}}" class="input-container input-container--checkbox">
                                <input disabled type="checkbox" @if ($hide_commercials) checked="checked" @endif name="hide_commercials">
                                <div class="input-container--checkbox__element"></div>
                                <div class="input-container__label">Скрыть рекламные блоки и анонсы</div>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="inner-page__content">
                    @if (!$other)
                    <div class="box">
                            <div class="box__heading">
                                <div class="box__heading__inner">
                                    {{$package->full_name}}
                                    @if ($package->author != "")<div class="interprogram-packages-list-item__author">Автор: <strong>{{$package->author}}</strong></div>@endif
                                </div>
                                <div class="box__heading__right">
                                    @if ($package->can_edit)
                                        <div class="interprogram-packages-list-item__options">
                                    <span class="button button--light button--dropdown" >
                                        <span class="button--dropdown__text">Действия</span>
                                        <span class="button--dropdown__icon">
                                            <i class="fa fa-chevron-down"></i>
                                        </span>
                                        <div class="button--dropdown__list">
                                            <a class="button--dropdown__list__item" href="/channels/{{$channel->id}}/graphics/edit/{{$package->id}}">Редактировать</a>
                                            <a class="button--dropdown__list__item" data-confirm-form-input-name="package_id" data-confirm-form-input-value="{{$package->id}}" data-confirm-form-text="Вы уверены, что хотите удалить пакет?" data-confirm-form-url="/graphics/delete">Удалить</a>
                                        </div>
                                    </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="box__inner">
                                <div class="interprogram-packages-list-item__inner">

                                    <div class="interprogram-packages-list-item__description">{!! $package->description !!}</div>
                                    <div class="interprogram-packages-list-item__videos">
                                        <div class="small-videos-list">
                                            @if ($package->visibleRecords &&  count($package->visibleRecords) > 0)
                                                @foreach($package->visibleRecords as $record)
                                                    @include('blocks/video_small', ['video' => $record, 'title' => $record->short_title])
                                                @endforeach
                                            @else
                                                @foreach($package->records as $record)
                                                    @include('blocks/video_small', ['video' => $record, 'title' => $record->short_title])
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                        </div>
                   </div>
                   <div class="row row--align-start">
                       @include('blocks/comments', ['class' => 'interprogram-page__comments', 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => \App\InterprogramPackage::TYPE_INTERPROGRAM, 'material_id' => $package->id]])
                   </div>
                @else
                    <div class="row ">
                        @include('blocks/records_list', ['class' => 'records-list__outer--full-page', 'conditions' => $records_conditions])
                    </div>
                @endif
                </div>
            </div>
            @if (!$other)
            <div class="col col--sidebar">
                @if (count($related) > 0)
                <div class="col">
                    <div class="box">
                        <div class="box__heading box__heading--small">
                            <div class="box__heading__inner">
                                Смотрите также
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="interprogram-page__related">
                                @foreach ($related as $item)
                                <a href="{{$item->full_url}}" class="record-item">
                                    <div class="record-item__cover" style="background-image: url({{$item->cover_with_random}})"></div>
                                    <div class="record-item__texts">
                                        <span class="record-item__title">
                                            {{$item->name != "" ? $item->name : $item->years_range}}
                                        </span>
                                        <div class="record-item__info">
                                            @if ($item->name != "")
                                                <span class="record-item__date">
                                                    <i class="fa fa-calendar"></i>{{$item->years_range}}
                                                 </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    @include('blocks/generic_sidebar', ['hide_articles' => true])
                </div>
             @endif
        </div>
    </div>
@endsection
