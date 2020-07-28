@extends('layouts.default')
@section('content')
    <div class="inner-page user-page" data-user-id="{{$user->id}}">
        <div class="user-page__top">
            <div class="user-page__left">
                @if ($user->avatar)
                    <img class="user-page__avatar" src="{{$user->avatar->url}}"/>
                @endif
                <div class="user-page__info-blocks">
                    <div class="user-page__info-block">
                        <div class="user-page__info-block__title">Замечания</div>
                        <a class="user-page__info-block__value user-page__info-block__value--warnings">{{$user->ban_level}}%</a>
                        @if (\App\Helpers\PermissionsHelper::allows('doban'))
                            <a class="user-page__info-block__change user-page__info-block__change--warnings">±</a>
                        @endif
                    </div>
                    <div class="user-page__info-block">
                        <div class="user-page__info-block__title">Репутация</div>
                        <a class="user-page__info-block__value user-page__info-block__value--reputation">{{$user->reputation_number}}</a>
                        @if ($user->can_change_reputation)
                            <a class="user-page__info-block__change user-page__info-block__change--reputation">±</a>
                        @endif
                    </div>
                    <div class="user-page__info-block">
                        <div class="user-page__info-block__title">Награды</div>
                        <a class="user-page__info-block__value user-page__info-block__value--awards">{{count($user->awards)}}</a>
                        @if (\App\Helpers\PermissionsHelper::allows('awado'))
                            <a class="user-page__info-block__change user-page__info-block__change--awards">±</a>
                        @endif
                    </div>
                </div>
            </div>
            @include('blocks/user_page_modals', ['user' => $user])
            <div class="user-page__info-container">
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
                <div class="inner-page__content user-page__info-container__inner">
                    <div class="box">
                        <div class="box__inner">
                            <div class="user-info">
                                <div class="user-info__col">
                                    <img class="user-info__group-icon" src="{{$user->group_icon}}" />
                                    <br>
                                    @if (\App\Helpers\PermissionsHelper::allows('usrepl') && auth()->user()->id != $user->id)
                                        <select data-user-id="{{$user->id}}" name="user_group" class="select-classic">
                                            @foreach(\App\UserGroup::all() as $group)
                                            <option @if ($group->id == $user->group_id) selected @endif value="{{$group->id}}">{{$group->name}}</option>
                                            @endforeach
                                        </select>
                                    @endif
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
                                    <div class="user-info__buttons">
                                        <a href="/forum/user-messages/{{$user->id}}" class="button button--flat">Посты на форуме</a>
                                        @if (auth()->user() && auth()->user()->id != $user->id)
                                            <a href="/pm/send?user_id={{$user->id}}" class="button button--flat">Отправить личное сообщение</a>
                                        @endif
                                        @if (auth()->user() && auth()->user()->id == $user->id)
                                            @if (\App\Helpers\PermissionsHelper::allows('admbar'))
                                                <a href="/admin" class="button button--flat">Панель администратора</a>
                                            @endif
                                            @if (\App\Helpers\PermissionsHelper::allows('redactorbar'))
                                                <a href="/redactor-panel" class="button button--flat">Панель редактора</a>
                                            @endif
                                        @endif
                                    </div>
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
        </div>

        @if (count($videos) > 0)
            <div class="box box--dark">
                <a href="/users/{{$user->id}}/videos" class="box__heading">
                    <div class="box__heading__inner">
                        Видео пользователя <span class="box__heading__count">{{count($videos)}}</span>
                    </div>
                </a>
                <div class="box__inner">
                    <div class="records-list records-list--thumbs records-list__outer--full-page ">
                        @foreach ($videos->take(12) as $record)
                            @include('blocks/record', ['record' => $record])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        @if (count($radio_recordings) > 0)
            <div class="box">
                <a href="/users/{{$user->id}}/radio" class="box__heading">
                    <div class="box__heading__inner">
                        Радиозаписи пользователя <span class="box__heading__count">{{count($radio_recordings)}}</span>
                    </div>
                </a>
                <div class="box__inner">
                    <div class="videos-list">
                        @foreach ($radio_recordings->take(12) as $record)
                            @include('blocks/radio_recording', ['record' => $record])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if (count($user->comments) > 0)
        <div class="row row--align-start">
            <div class="col">
                <div class="box">
                    <a href="/users/{{$user->id}}/comments" class="box__heading">
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
        @endif
    </div>
@endsection
