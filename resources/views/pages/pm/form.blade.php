@extends('layouts.default', ['vue' => true])
@section('content')
    <div class="private-messages box">
        <div class="box__heading">
            <div class="box__heading__inner">
                Написать новое сообщение
            </div>

            <div class="box__heading__buttons"><a class="button button--light" href="{{route('pm.index')}}">Назад</a>
            </div>
        </div>
        <div class="box__inner">
            <form method="POST" class="form">
                <div class="form__content">
                    <div class="input-container">
                        <label class="input-container__label">Пользователь<span
                                class="input-container__required">*</span></label>
                        <div class="input-container__element-outer">
                            <div class="input-container__inner">
                                <select name="to_id" id="users_autocomplete">
                                    @if ($user)
                                        <option value="{{$user->id}}">{{$user->username}}</option>
                                    @endif
                                </select>
                                <span class="input-container__message"></span>
                            </div>
                            @if ($can_mass_send)
                                <input type="hidden" name="is_group" value="0"/>
                                <div class="input-container__toggle-buttons">
                                    <a class="input-container__toggle-button input-container__toggle-button--mass-send">Групповая
                                        рассылка</a>
                                </div>
                                <div style="display:none" id="users_groups_select_container">
                                    @include('blocks.forms.user-groups-select', ['name' => 'group_ids', 'data' => "0", 'default_settings' => false])
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
                        <label class="input-container__label">Текст<span
                                class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            @include('blocks.bb-editor.main', ['name' => 'text'])
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

