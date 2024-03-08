@extends('layouts.default')
@section('content')
<div class="users-list">
    <div class="users-list__heading">Список пользователей</div>
    <form method="GET" class="users-list__form">
        @csrf
        <div class="users-list__form__inner">
        <div class="users-list__form__item">
            <input class="input" value="{{$search}}" name="search">
        </div>
        @if ($is_moderator)
        <div class="users-list__form__item">
            <select class="select-classic" name="search_field">
                <option value="username">Логин</option>
                <option value="ip_address_reg">IP адрес</option>
                <option value="email">E-mail адрес</option>
            </select>
        </div>
        @endif
        <div class="users-list__form__item">
            <select class="select-classic" name="group_id">
                <option value="0" @if ($group_id == 0) selected @endif>Все группы</option>
                @foreach (\App\UserGroup::all() as $user_group)
                <option value="{{$user_group->id}}"  @if ($group_id == $user_group->id) selected @endif>{{$user_group->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="users-list__form__item">
            <select class="select-classic" name="sort_by">
                <option value="username">Сортировать по</option>
                <option value="username" @if ($sort_by == "username") selected @endif>Логин</option>
                <option value="group_id" @if ($sort_by == "group_id") selected @endif>Группа</option>
                <option value="created_at" @if ($sort_by == "created_at") selected @endif>Дата регистрации</option>
                <option value="was_online" @if ($sort_by == "was_online") selected @endif>Дата входа</option>
            </select>
        </div>
        <div class="users-list__form__item">
            <select class="select-classic" name="on_page">
                @for($i = 1; $i <= 10; $i++)
                    <option @if ($on_page == $i * 10) selected @endif value="{{$i * 10}}">{{$i * 10}}</option>
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
    <div class="users-list__pager">
        {{$users->links()}}
    </div>
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
                <td>Город</td>
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
                    <a class="users-list__link" href="/index/8-{{$user->id}}">{{$user->username}}</a>
                </td>
                @if ($is_moderator)
                <td>
                    {{$user->ip_address}}
                </td>
                @endif
                <td>
                   <div class="users-list__group-icon-container">
                        {!!  $user->group_icon !!}
                    </div>
                </td>
                <td>
                    {{$user->was_online}}
                </td>
                <td>
                    {{$user->created_at}}
                </td>
                <td>
                    {{$user->meta ? $user->meta->city : ""}}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
