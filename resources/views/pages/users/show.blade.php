@extends('layouts.default')
@section('content')
    <div class="inner-page user-page" data-user-id="{{$user->id}}">
        <div class="user-page__top">
            @if ($user->avatar)
            <img class="user-page__avatar" src="{{$user->avatar->url}}"/>
            @endif
            <div class="user-page__info-container">
                <div class="row row--vertical">
                    <div class="inner-page__header">
                        <div class="inner-page__header__title">Пользователь <strong>{{$user->username}}</strong></div>
                        <div class="user-info__right">
                            @if (auth()->user() && $user->id == auth()->user()->id)
                            <a href="/profile/edit" class="button">Изменить профиль</a>
                            @elseif (\App\Helpers\PermissionsHelper::allows('usedita'))
                            <a href="/profile/edit/{{$user->id}}" class="button">Изменить профиль</a>
                            @endif
                        </div>
                    </div>
                    <div class="inner-page__content">
                        <div class="user-info">
                            <div class="user-info__col">
                                <img class="user-info__group-icon" src="{{$user->group_icon}}" />
                                @if($banned_till)
                                    <div class="user-info__ban">Пользователь заблокирован до {{$banned_till}}</div>
                                @elseif ($is_banned_forever)
                                    <div class="user-info__ban">Пользователь заблокирован надолго, т.к. признан ботом или злостным нарушителем.</div>
                                @endif
                                <div class="user-info__item">
                                    <strong>Имя: </strong>{{$user->name}}
                                </div>
                                <div class="user-info__item">
                                    <strong>Дата регистрации: </strong>{{$user->created_at}}
                                </div>
                                <div class="user-info__item">
                                    <strong>Был на сайте: </strong>{{$user->was_online}}
                                </div>
                                @if (auth()->user() && auth()->user()->id != $user->id)
                                <a href="/pm/send?user_id={{$user->id}}" class="button button--flat">Отправить личное сообщение</a>
                                @endif
                            </div>
                            <div class="user-info__col">
                                @if ($user->meta->date_of_birth)
                                    <div class="user-info__item">
                                        <div class="user-info__item__icon"><i class="fa fa-birthday-cake"></i></div>
                                        {{$user->meta->date_of_birth_formatted}}
                                    </div>
                                @endif
                                @if ($user->meta->yandex_video)
                                <div class="user-info__item">
                                    <div class="user-info__item__icon"><i class="fa fa-play"></i></div>
                                    {{$user->meta->yandex_video}}
                                </div>
                                @endif
                                @if ($user->meta->vk)
                                    <div class="user-info__item">
                                        <div class="user-info__item__icon"><i class="fab fa-vk"></i></div>
                                        {{$user->meta->vk}}
                                    </div>
                                @endif
                                @if ($user->meta->youtube)
                                    <div class="user-info__item">
                                        <div class="user-info__item__icon"><i class="fab fa-youtube"></i></div>
                                        {{$user->meta->youtube}}
                                    </div>
                                @endif
                                @if ($user->meta->facebook)
                                    <div class="user-info__item">
                                        <div class="user-info__item__icon"><i class="fab fa-facebook"></i></div>
                                        {{$user->meta->facebook}}
                                    </div>
                               @endif
                            </div>
                        </div>
                        @if ($user->signature != "")
                        <div class="user-page__signature">
                            {!! $user->signature !!}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="user-page__info-block">
                    <div class="user-page__info-block__title">Репутация</div>
                    <a class="user-page__info-block__value user-page__info-block__value--reputation">{{$user->reputation_number}}</a>
                    @if ($user->can_change_reputation)
                    <a class="user-page__info-block__change user-page__info-block__change--reputation">±</a>
                    @endif
                </div>
            </div>
            <div class="col">
                <div class="user-page__info-block">
                    <div class="user-page__info-block__title">Награды</div>
                    <a class="user-page__info-block__value user-page__info-block__value--awards">{{count($user->awards)}}</a>
                    @if (\App\Helpers\PermissionsHelper::allows('awado'))
                        <a class="user-page__info-block__change user-page__info-block__change--awards">±</a>
                    @endif
                </div>
            </div>
            <div class="col">
                <div class="user-page__info-block">
                    <div class="user-page__info-block__title">Замечания</div>
                    <a class="user-page__info-block__value user-page__info-block__value--warnings">{{$user->ban_level}}%</a>
                    @if (\App\Helpers\PermissionsHelper::allows('doban'))
                        <a class="user-page__info-block__change user-page__info-block__change--warnings">±</a>
                    @endif
                </div>
            </div>
            @include('blocks/user_page_modals', ['user' => $user])
        </div>
        @if (count($videos) > 0)
        <div class="row">
            <div class="box">
                <div class="box__heading">
                    <div class="box__heading__inner">
                        Видео пользователя <span class="box__heading__count">{{count($videos)}}</span>
                    </div>
                </div>
                <div class="box__inner">
                    <div class="videos-list">
                        @foreach ($videos->take(10) as $record)
                            @include('blocks/record', ['record' => $record])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if (count($radio_recordings) > 0)
            <div class="row">
                <div class="box">
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            Радиозаписи пользователя <span class="box__heading__count">{{count($radio_recordings)}}</span>
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="videos-list">
                            @foreach ($radio_recordings->take(10) as $record)
                                @include('blocks/record', ['record' => $record])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row row--align-start">
            <div class="col">
                <div class="box">
                    <a href="/index/34-{{$user->id}}" class="box__heading">
                        <div class="box__heading__inner">
                            Комментарии пользователя <span class="box__heading__count">{{count($user->comments)}}</span>
                        </div>
                    </a>
                    <div class="box__inner">
                        <div class="comments">
                            @foreach ($user->comments->take(10) as $comment)
                                @include('blocks/comment', ['show_link' => true, 'comment' => $comment])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection