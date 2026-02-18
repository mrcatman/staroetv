@extends('layouts.default')
@section('content')
    <div class="box">
        <div class="box__heading">
            <h1 class="box__heading__inner">Список пользователей</h1>
        </div>
        <div class="box__inner">
            <form method="GET" class="users-list__form">
                @csrf
                <div class="users-list__form__inner">
                    <div class="users-list__form__item">
                        <input class="input" value="{{$search}}" placeholder="Поиск..." name="search">
                    </div>
                    @if ($is_moderator)
                        <div class="users-list__form__item">
                            <select class="select-classic" name="search_field">
                                <option value="username">По логину</option>
                                <option value="ip_address_reg">По IP адресу</option>
                                <option value="email">По e-mail адресу</option>
                            </select>
                        </div>
                    @endif
                    <div class="users-list__form__item">
                        <select class="select-classic" name="group_id">
                            <option value="0" @if ($group_id == 0) selected @endif>Все группы</option>
                            @foreach (\App\Models\UserGroup::all() as $user_group)
                                <option value="{{$user_group->id}}"
                                        @if ($group_id == $user_group->id) selected @endif>{{$user_group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="users-list__form__item">
                        <select class="select-classic" name="sort_by">
                            <option value="username" @if ($sort_by == "username") selected @endif>Сортировка по логину
                            </option>
                            <option value="group_id" @if ($sort_by == "group_id") selected @endif>Сортировка по группе
                            </option>
                            <option value="created_at" @if ($sort_by == "created_at") selected @endif>Сортировка по дате
                                регистрации
                            </option>
                            <option value="was_online" @if ($sort_by == "was_online") selected @endif>Сортировка по дате
                                входа
                            </option>
                        </select>
                    </div>
                    <div class="users-list__form__item">
                        <select class="select-classic" name="on_page">
                            @for($i = 1; $i <= 10; $i++)
                                <option @if ($on_page == $i * 10) selected
                                        @endif value="{{$i * 10}}">{{$i * 10}}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="users-list__form__item">
                        <button type="submit" class="button button--light">Ок</button>
                    </div>
                </div>
                <div class="users-list__form__found">
                    Найдено: <strong>{{$total}}</strong> пользователей
                </div>
            </form>

            {{$users->links()}}

                <table class="users-list__table">
                    <thead>
                    <tr>
                        <td>Аватарка</td>
                        <td>Никнейм</td>
                        @if ($is_moderator)
                            <td>IP-адрес</td>
                        @endif
                        <td>Группа</td>
                        <td>Дата входа</td>
                        <td>Дата регистрации</td>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                @if ($user->avatar)
                                    <img class="users-list__avatar" src="{{$user->avatar->url}}"/>
                                @endif
                            </td>
                            <td>
                                <a class="users-list__link" href="{{route('users.show', $user)}}">{{$user->username}}</a>
                            </td>
                            @if ($is_moderator)
                                <td>
                                    <span class="users-list__table__label">IP-адрес:&nbsp;</span>
                                    {{$user->ip_address}}
                                </td>
                            @endif
                            <td>
                                <div class="users-list__group-icon-container">
                                    {!!  $user->group_icon !!}
                                </div>
                            </td>
                            <td>
                                <span class="users-list__table__label">Дата входа:&nbsp;</span>{{$user->was_online}}
                            </td>
                            <td>
                                <span class="users-list__table__label">Дата регистрации:&nbsp;</span>{{$user->created_at}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            {{$users->links()}}
        </div>

    </div>
@endsection
