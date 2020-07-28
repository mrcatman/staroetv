@extends('layouts.default')
@section('content')
    <div class="private-messages box">
        <div class="box__heading">
            Написать новое сообщение
            <div class="box__heading__right box__heading__right--full-width"><a class="button" href="/pm">Назад</a></div>
       </div>
        <div class="private-messages__inner box__inner">
            <form method="POST" class="form">
                <div class="input-container">
                    <label class="input-container__label">Пользователь<span class="input-container__required">*</span></label>
                    <div class="input-container__element-outer">
                        <div class="input-container__overlay-outer">
                            <div class="input-container__disabled-overlay" style="display: none"></div>
                            <div class="input-container__inner">
                                <select name="to_id" id="users_autocomplete">
                                    @if ($user)
                                        <option value="{{$user->id}}">{{$user->username}}</option>
                                    @endif
                                </select>
                                <span class="input-container__message"></span>
                            </div>
                        </div>
                        @if ($can_mass_send)
                            <input type="hidden" name="is_group" value="0"/>
                            <div class="input-container__toggle-buttons">
                                <a class="input-container__toggle-button input-container__toggle-button--mass-send" >Групповая рассылка</a>
                            </div>
                            <div style="display:none" id="users_groups_select_container">
                                @include('blocks/user_groups_select', ['name' => 'group_ids', 'data' => "0", 'default_settings' => false])
                            </div>
                        @endif
                    </div>
                </div>

                <div class="input-container">
                    <label class="input-container__label">Заголовок</label>
                    <div class="input-container__inner">
                        <input class="input" name="title" value=""/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Текст<span class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        @include('blocks/bb_editor', ['name' => 'text'])
                        <span class="input-container__message"></span>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

