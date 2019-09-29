@extends('layouts.default')
@section('content')
    <div class="inner-page user-page">
        <div class="user-page__top">
            @if ($user->avatar)
            <img class="user-page__avatar" src="{{$user->avatar->url}}"/>
            @endif
            <div class="user-page__info-container">
                <div class="row row--vertical">
                    <div class="inner-page__header">
                        <div class="inner-page__header__title">Пользователь <strong>{{$user->username}}</strong></div>
                        <img class="user-info__group-icon" src="{{$user->group_icon}}" />
                    </div>
                    <div class="inner-page__content">
                        <div class="user-info">
                            <div class="user-info__col">

                                <div class="user-info__item">
                                    <strong>Имя: </strong>{{$user->name}}
                                </div>
                                <div class="user-info__item">
                                    <strong>Дата регистрации: </strong>{{$user->created_at}}
                                </div>
                                <div class="user-info__item">
                                    <strong>Был на сайте: </strong>{{$user->was_online}}
                                </div>
                            </div>
                            <div class="user-info__col">
                                @if ($user->meta->date_of_birth)
                                    <div class="user-info__item">
                                        <div class="user-info__item__icon"><i class="fa fa-birthday-cake"></i></div>
                                        {{$user->meta->date_of_birth}}
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
            @if (\App\Helpers\PermissionsHelper::allows("readrep"))
            <div class="col">
                <div class="user-page__info-block">
                    <div class="user-page__info-block__title">Репутация</div>
                    <a class="user-page__info-block__value user-page__info-block__value--reputation">{{$user->reputation_number}}</a>
                    @if ($user->can_change_reputation)
                    <a class="user-page__info-block__change user-page__info-block__change--reputation" data-user-id="{{$user->id}}">±</a>
                    @endif
                </div>
            </div>
            @endif
            <div class="col">
                <div class="user-page__info-block">
                    <div class="user-page__info-block__title">Награды</div>
                    <div class="user-page__info-block__value user-page__info-block__value--awards">{{count($user->awards)}}</div>
                </div>
            </div>
            <div class="col">
                <div class="user-page__info-block">
                    <div class="user-page__info-block__title">Баны</div>
                    <div class="user-page__info-block__value user-page__info-block__value--warnings">{{$user->ban_level}}%</div>
                </div>
            </div>
            @include('blocks/user_page_modals', ['user' => $user])
        </div>
        @if (count($user->videos) > 0)
        <div class="row">
            <div class="box">
                <div class="box__heading">
                    Видео пользователя <span class="box__heading__count">{{count($user->videos)}}</span>
                </div>
                <div class="box__inner">
                    <div class="videos-list">
                        @foreach ($user->videos->take(10) as $video)
                            @include('blocks/video', ['video' => $video])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="row row--align-start">
            <div class="col">
                <div class="box">
                    <div class="box__heading">
                        Комментарии пользователя <span class="box__heading__count">{{count($user->comments)}}</span>
                    </div>
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