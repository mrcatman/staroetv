@extends('layouts.default')
@section('page-title')
    {{$program->name}}
@endsection
@section('content')
    <div class="inner-page program-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="breadcrumbs">
                    @if ($program->channel)
                    <a class="breadcrumbs__item" href="{{$program->channel->is_radio ? "/radio" : "/video"}}">Архив</a>
                    <a class="breadcrumbs__item" href="{{$program->channel->full_url}}">{{$program->channel->name}}</a>
                    @endif
                    <a class="breadcrumbs__item breadcrumbs__item--current">{{$program->name}}</a>
                </div>
                <div class="inner-page__header">
                    <div class="inner-page__header__title">{{$program->name}}</div>
                    @if ($program->can_edit || \App\Helpers\PermissionsHelper::allows('contentapprove'))
                        <span class="button button--light button--dropdown" >
                            <span class="button--dropdown__text">Действия</span>
                            <span class="button--dropdown__icon">
                                <i class="fa fa-chevron-down"></i>
                            </span>
                            <div class="button--dropdown__list">
                                  @if ($program->can_edit)
                                <a class="button--dropdown__list__item" href="/programs/{{$program->id}}/edit">Редактировать</a>
                                @endif
                                @if (\App\Helpers\PermissionsHelper::allows('contentapprove'))
                                <a class="button--dropdown__list__item" data-approve="programs" data-approve-id="{{$program->id}}">{{$program->pending ? "Одобрить" : "Скрыть"}}</a>
                                @endif
                                @if ($program->can_edit)
                                <a class="button--dropdown__list__item" data-confirm-form-input-name="program_id" data-confirm-form-input-value="{{$program->id}}" data-confirm-form-text="Вы уверены, что хотите удалить программу?" data-confirm-form-url="/programs/delete">Удалить</a>
                                @endif
                            </div>
                        </span>
                    @endif
                </div>
                <div class="inner-page__content">
                    <div class="inner-page__text-block">
                        <div class="program-page__inner">
                            @if($program->coverPicture)
                                <img class="program-page__logo" src="{{$program->coverPicture->url}}">
                            @endif
                            <div class="program-page__info">
                                @if ($program->description != "")
                                    {!! $program->description !!}
                                @else
                                    <div class="program-page__no-description">Описание программы еще не заполнено</div>
                                @endif
                                    <div class="program-page__channels">
                                        @foreach ($program->channels_history as $program_channel)
                                            <a href="{{$program_channel['url']}}" class="program__channel__name">
                                                @if ($program_channel['logo'])
                                                    <img class="program__channel__logo" src="{{$program_channel['logo']}}"/>
                                                @endif
                                                {{$program_channel['name']}}
                                            </a>
                                        @endforeach
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @include('blocks/records_list', ['conditions' => $records_conditions])
                </div>

                @if (count($program->design) > 0)
                <div class="box interprogram-packages-list-item" >
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            Оформление программы
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="interprogram-packages-list-item__inner">
                             <div class="interprogram-packages-list-item__videos">
                                <div class="small-videos-list">
                                    @foreach($program->design as $record)
                                        @include('blocks/video_small', ['video' => $record, 'title' => $record->short_title])
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="row">
                    @include('blocks/comments', ['class' => 'program-page__comments', 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => \App\Program::TYPE_PROGRAMS, 'material_id' => $program->id]])
                </div>
            </div>
            <div class="col col--sidebar">
                @include('blocks/generic_sidebar', ['hide_articles' => true, 'is_radio' => $program->channel ? $program->channel->is_radio : false])
            </div>
        </div>
    </div>

@endsection
