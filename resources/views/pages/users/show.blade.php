@extends('layouts.default')
@section('content')
<div class="col">
    <div class="user-page" data-user-id="{{$user->id}}">
        <div class="user-page__top">
            <div class="user-page__left">
                @if ($user->avatar)
                    <img class="user-page__avatar" src="{{$user->avatar->url}}"/>
                @endif
                <div class="box">
                    <div class="box__inner">
                        <div class="user-page__info-blocks">
                            <div class="user-page__info-block">
                                <div class="user-page__info-block__title">Замечания</div>
                                <a class="user-page__info-block__value user-page__info-block__value--warnings">{{$user->ban_level}}
                                    %</a>
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
                </div>

            </div>
            @include('blocks.reputation.change-modal', ['user' => $user])
            <div class="user-page__info-container">
                <div class="inner-page__content user-page__info-container__inner">
                    <div class="box">
                        <div class="box__heading">
                            <h1 class="box__heading__inner">
                                <span>Пользователь <strong>{{$user->username}}</strong></span>
                            </h1>
                            @if ((auth()->user() && $user->id == auth()->user()->id) || \App\Helpers\PermissionsHelper::allows('usedita'))
                                <div class="box__heading__buttons">
                                    <div class="buttons-row">

                                        <a href="{{$user->id == auth()->user()->id ? route('profile.edit') : route('profile.edit.user', $user)}}"
                                           class="button">
                                            <i class="fa fa-edit"></i>
                                            Обновить профиль
                                        </a>
                                        @if ($user->id == auth()->user()->id)
                                            <a href="{{route('profile.edit-password')}}" class="button">
                                                <i class="fa fa-key"></i>
                                                Сменить пароль
                                            </a>
                                        @endif

                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="box__inner">
                            @if (\Session::has('after_confirm'))
                                <div class="response response--success">
                                    E-mail адрес подтвержден
                                </div>
                            @endif
                            <div class="row row--align-start user-info">
                                <div class="col user-info__col">
                                    <div class="user-info__group-icon-container">
                                        {!!  $user->group_icon !!}
                                    </div>
                                    @if (\App\Helpers\PermissionsHelper::allows('usrepl') && auth()->user()->id != $user->id)
                                        <select data-user-id="{{$user->id}}" name="user_group" class="select-classic">
                                            @foreach(\App\Models\UserGroup::all() as $group)
                                                <option @if ($group->id == $user->group_id) selected
                                                        @endif value="{{$group->id}}">{{$group->name}}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @if($banned_till)
                                        <div class="user-info__ban">Пользователь заблокирован до {{$banned_till}}</div>
                                    @elseif ($is_banned_forever)
                                        <div class="user-info__ban">Пользователь заблокирован надолго, т.к. признан
                                            ботом или злостным нарушителем.
                                        </div>
                                    @endif
                                    @if ($user->name != '-')
                                        <div class="user-info__item">
                                            <strong>Имя: </strong>{{$user->name}}
                                        </div>
                                    @endif
                                    <div class="user-info__item">
                                        <strong>Дата регистрации: </strong>{{$user->created_at}}
                                    </div>
                                    <div class="user-info__item">
                                        <strong>Был на
                                            сайте: </strong>{{$user->was_online ? $user->was_online : 'никогда'}}
                                    </div>
                                    <div class="user-info__buttons">
                                        @if ($user->forum_messages_count > 0)
                                        <a href="{{route('forum.user-messages', $user)}}" class="button">
                                            <i class="fa fa-comment"></i>
                                            Посты на форуме ({{$user->forum_messages_count}})
                                        </a>
                                        @endif
                                        @if (auth()->user() && auth()->user()->id != $user->id)
                                            <a href="{{route('pm.add', ['user_id' => $user->id])}}" class="button">
                                                <i class="fa fa-envelope"></i>
                                                Отправить личное сообщение
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col user-info__col">
                                    @if ($user->meta->date_of_birth)
                                        <div class="user-info__item user-info__item--with-icon">
                                            <div class="user-info__item__icon"><i class="fa fa-birthday-cake"></i></div>
                                            {{$user->meta->date_of_birth_formatted}}
                                        </div>
                                    @endif
                                    @if ($user->meta->yandex_video)
                                        <div class="user-info__item user-info__item--with-icon">
                                            <div class="user-info__item__icon"><i class="fa fa-play"></i></div>
                                            {{$user->meta->yandex_video}}
                                        </div>
                                    @endif
                                    @if ($user->meta->vk)
                                        <div class="user-info__item user-info__item--with-icon">
                                            <div class="user-info__item__icon"><i class="fab fa-vk"></i></div>
                                            {{$user->meta->vk}}
                                        </div>
                                    @endif
                                    @if ($user->meta->youtube)
                                        <div class="user-info__item user-info__item--with-icon">
                                            <div class="user-info__item__icon"><i class="fab fa-youtube"></i></div>
                                            {{$user->meta->youtube}}
                                        </div>
                                    @endif
                                    @if ($user->meta->facebook)
                                        <div class="user-info__item user-info__item--with-icon">
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

    @if ($videos_count > 0)
        <div class="box box--dark">
            <a href="{{route('users.videos', $user->id)}}" class="box__heading">
                <div class="box__heading__inner">
                    Видео пользователя&nbsp;<span class="box__heading__count">{{$videos_count}}</span>
                </div>
            </a>
            <div class="box__inner">
                <div class="records-list records-list--thumbs ">
                    @foreach ($videos as $record)
                        @include('blocks.records.item', ['record' => $record])
                    @endforeach
                </div>

            </div>
        </div>
    @endif
    @if ($radio_recordings_count > 0)
        <div class="box">
            <a href="{{route('users.radio-recordings', $user->id)}}" class="box__heading">
                <div class="box__heading__inner">
                    Радиозаписи пользователя&nbsp;<span class="box__heading__count">{{$radio_recordings_count}}</span>
                </div>
            </a>
            <div class="box__inner">
                <div class="records-list">
                    @foreach ($radio_recordings as $record)
                        @include('blocks.records.radio-item', ['record' => $record])
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if (count($user->comments) > 0)
        <div class="row row--align-start">
            <div class="col">
                <div class="box">
                    <a href="{{route('comments.user', $user->id)}}" class="box__heading">
                        <div class="box__heading__inner">
                            Комментарии пользователя&nbsp;<span
                                class="box__heading__count">{{count($user->comments)}}</span>
                        </div>
                    </a>
                    <div class="box__inner">
                        <div class="comments">
                            @foreach ($user->comments->take(10) as $comment)
                                @include('blocks.comments.item', ['show_link' => true, 'comment' => $comment])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
