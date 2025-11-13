@section('page-title')
    @if ($other)
        Заставки канала {{$channel->all_names_with_main}}
    @else
        {{$package->full_name}}
    @endif
@endsection
@extends('layouts.default')
@section('content')
    <div class="row row--align-start">
        <div class="col col--2-5">
            <div class="box">
                <div class="box__breadcrumbs">
                    <div class="breadcrumbs">
                        <a class="breadcrumbs__item" href="/{{$channel->is_radio ? "radio" : "video"}}">Архив</a>
                        <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
                        <a class="breadcrumbs__item" href="{{$channel->full_url}}#interprogram">Оформление</a>
                        <a class="breadcrumbs__item breadcrumbs__item--current">{{$package->full_name}}</a>
                    </div>
                </div>
                <div class="box__heading">
                    @if ($other)
                        <div class="box__heading__inner">Заставки канала {{$channel->all_names_with_main}}</div>
                        <div class="box__heading__right">
                            <a href="{{$base_link}}?hide_unsorted={{$hide_unsorted ? 0 : 1}}"
                               class="input-container input-container--checkbox">
                                <input disabled type="checkbox" @if ($hide_unsorted) checked="checked"
                                       @endif name="hide_unsorted">
                                <div class="input-container--checkbox__element"></div>
                                <div class="input-container__label">Скрыть рекламные блоки и анонсы</div>
                            </a>
                        </div>
                </div>
                @else
                    <div class="box__heading__inner">
                        <div>
                            {{$package->full_name}}
                            @if ($package->author != "")

                                <div class="interprogram-packages-list-item__author">
                                    Автор:&nbsp;<strong>{{$package->author}}</strong>
                                </div>
                            @endif
                        </div>

                    </div>
                    @if ($package->can_edit)
                        <div class="box__heading__right">
                                <span class="button button--light button--dropdown">
                                    <span class="button--dropdown__text">Действия</span>
                                    <span class="button--dropdown__icon">
                                        <i class="fa fa-chevron-down"></i>
                                    </span>
                                    <div class="button--dropdown__list">
                                        <a class="button--dropdown__list__item"
                                           href="/channels/{{$channel->id}}/graphics/edit/{{$package->id}}">Редактировать</a>
                                            <a class="button--dropdown__list__item"
                                               data-confirm-form-input-value="{{$package->id}}"
                                               data-confirm-form-text="Вы уверены, что хотите удалить пакет?"
                                               data-confirm-form-url="/graphics/delete">Удалить</a>
                                        </div>
                                    </span>
                        </div>
                    @endif
                @endif
            </div>
            @if (!$other)
                <div class="box__inner">

                    <div class="interprogram-packages-list-item__inner">
                        @if ($package->descriptiion != '')
                            <div
                                class="interprogram-packages-list-item__description">{!! $package->description !!}
                            </div>
                        @endif
                        <div class="interprogram-packages-list-item__videos">
                            @foreach($annotations as $annotation)
                                <div class="interprogram-packages-list-item__section">
                                    @if ($annotation['annotation'])
                                        <div class="interprogram-annotation">
                                            <div
                                                class="interprogram-annotation__title">{{$annotation['annotation']->title}}</div>
                                            <div
                                                class="interprogram-annotation__text">{{$annotation['annotation']->text}}</div>
                                        </div>
                                    @endif
                                    <div class="records-list records-list--thumbs">
                                        @foreach ($annotation['records'] as $record)
                                            @include('blocks.records.item')
                                        @endforeach
                                    </div>
                                </div>

                            @endforeach

                        </div>

                    </div>
                </div>
        </div>
        @include('blocks/comments', [ 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => \App\Models\InterprogramPackage::TYPE_INTERPROGRAM, 'material_id' => $package->id]])

        @else

            @include('blocks.records.list', ['conditions' => $records_conditions])
        @endif
    </div>

    <div class="col col--sidebar">
        @include('blocks.interprogram.related')
        @include('blocks/generic_sidebar', ['hide_articles' => true])
    </div>

@endsection
