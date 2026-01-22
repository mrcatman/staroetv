@extends('layouts.default')
@section('content')
    <div class="inner-page channel-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="box">
                    <div class="box__breadcrumbs">
                        <div class="breadcrumbs">
                            <a class="breadcrumbs__item"
                               href="{{typed_route('records.[RECORD].index', $program->channel->is_radio)}}">Архив</a>
                            <a class="breadcrumbs__item"
                               href="{{$program->channel->full_url}}">{{$program->channel->name}}</a>
                            <a class="breadcrumbs__item" href="{{$program->full_url}}">{{$program->name}}</a>
                            <a class="breadcrumbs__item breadcrumbs__item--current">Оформление</a>
                        </div>
                    </div>
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            Заставки программы {{$program->name}}
                        </div>
                        @if(\App\Helpers\PermissionsHelper::allows('additionalown'))
                            <div class="box__heading__buttons">
                                <a class="button" href="{{route('design.programs.add', $program)}}"><i
                                        class="fa fa-plus"></i>Добавить</a>
                            </div>
                        @endif
                    </div>

                    @foreach ($packages as $package)
                        <div class="interprogram-packages-list-item" id="package_{{$package->id}}">
                            @if ($package->name || $package->years_range)
                                <div class="box__heading">

                                    <div class="box__heading__inner">
                                        {{$package->name != "" ? ($package->name . ($package->years_range != "" ? " (".$package->years_range.")" : "")) : $package->years_range}}
                                    </div>

                                    <div class="box__heading__right">
                                        @if ($package->can_edit && !$package->is_other)
                                            <div class="interprogram-packages-list-item__options">
                                                <span class="button button--light button--dropdown">
                                                    <span class="button--dropdown__text">Действия</span>
                                                    <span class="button--dropdown__icon">
                                                        <i class="fa fa-chevron-down"></i>
                                                    </span>
                                                    <div class="button--dropdown__list">
                                                        <a class="button--dropdown__list__item"
                                                           href="{{route('design.programs.edit', [$program->id, $package->id])}}">Редактировать</a>
                                                        <a class="button--dropdown__list__item"
                                                           data-confirm-form-input-value="{{$package->id}}"
                                                           data-confirm-form-text="Вы уверены?"
                                                           data-confirm-form-url="{{route('design.delete')}}">Удалить</a>
                                                    </div>
                                             </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="box__inner">
                                <div class="interprogram-packages-list-item__inner">

                                    @if ($package->description != '' || $package->author != '')
                                    <div
                                        class="interprogram-packages-list-item__description">
                                        {!! $package->description !!}

                                        @if ($package->author != "")
                                            <br/><i>Создатель(-и):&nbsp;<strong>{{$package->author}}</strong></i>
                                        @endif
                                    </div>
                                    @endif

                                    <div class="interprogram-packages-list-item__videos">
                                        <div class="records-list records-list--thumbs">
                                            @if ($package->visibleRecords &&  count($package->visibleRecords) > 0)
                                                @foreach($package->visibleRecords as $record)
                                                    @include('blocks.records.item')
                                                @endforeach
                                            @else
                                                @foreach($package->records as $record)
                                                    @include('blocks.records.item')
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if (count($related_programs) > 0)
                <div class="col col--sidebar">

                    <div class="box">
                        <div class="box__heading box__heading--small">
                            <div class="box__heading__inner">
                                Смотрите также
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="interprogram-page__related">
                                <div class="records-list">
                                    @foreach ($related_programs as $program)
                                        @include('blocks.records.item', ['record' => $program, 'url' => route('design.programs.show', $program->url ?? $program->id), 'title' => $program->name, 'hide_info' => true])
                                    @endforeach
                                </div>

                            </div>
                        </div>
                    </div>
                    @endif
                    @include('blocks.global.generic-sidebar', ['hide_articles' => true])
                </div>
        </div>
@endsection
