@extends('layouts.default')
@section('content')
    <form class="form box" action="/profile/edit" method="POST">
        <div class="box__heading">
            Редактирование личной информации
        </div>
        <div class="box__inner">
            <div class="response"></div>
            @if (isset($edit_id))
                <input type="hidden" name="user_id" value="{{$edit_id}}"/>
            @endif
            <div class="inputs-group">
                <div class="inputs-group__title">Основная информация</div>
                <div class="inputs-group__contents">
                    <div class="input-container">
                        <label class="input-container__label">Ник<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input class="input" name="username" value="{{$user->username}}"/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">E-mail адрес<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input class="input" name="email" required type="email" value="{{$user->email}}"/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="inputs-group">
                <div class="inputs-group__title">Личная информация</div>
                <div class="inputs-group__contents">
                    <div class="input-container">
                        <label class="input-container__label">Ваше имя</label>
                        <div class="input-container__inner">
                            <input class="input" name="name" value="{{$user->name}}"/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Дата рождения</label>
                        <div class="input-container__inner">
                            <datepicker name="date_of_birth" :value="{{$user->meta->date_of_birth_ts}}" />
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Страна</label>
                        <div class="input-container__inner">
                            <select2 name="country" :options="{{json_encode($countries)}}" :value="{{$user->meta ? $user->meta->country : "169"}}" />
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Город</label>
                        <div class="input-container__inner">
                            <input class="input" name="city" value="{{$user->meta ? $user->meta->city : ""}}" />
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="inputs-group">
                <div class="inputs-group__title">Форум</div>
                <div class="inputs-group__contents">
                    <div class="input-container">
                       <label class="input-container__label">Аватарка</label>
                       <div class="input-container__inner">
                           <picture-uploader name="avatar_id" :data="{{$user->avatar ? $user->avatar : "null"}}" />
                           <span class="input-container__message"></span>
                       </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Подпись</label>
                        <div class="input-container__inner">
                            <div class="input-container__element-outer">
                                <textarea class="input" name="signature">{{$user->signature_original}}</textarea>
                                <span class="input-container__message"></span>
                                <div class="input-container__description">BB-коды разрешены</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="inputs-group">
                <div class="inputs-group__title">Ссылки</div>
                <div class="inputs-group__contents">
                    <div class="input-container">
                        <label class="input-container__label">ВК</label>
                        <div class="input-container__inner">
                            <input class="input" name="vk" value="{{$user->meta ? $user->meta->vk : ""}}" />
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Youtube</label>
                        <div class="input-container__inner">
                            <input class="input" name="youtube" value="{{$user->meta ? $user->meta->youtube : ""}}" />
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Dailymotion</label>
                        <div class="input-container__inner">
                            <input class="input" name="yandex_video" value="{{$user->meta ? $user->meta->yandex_video : ""}}" />
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Facebook</label>
                        <div class="input-container__inner">
                            <input class="input" name="facebook" value="{{$user->meta ? $user->meta->facebook : ""}}" />
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
            </div>

            <button class="button">Сохранить</button>
        </div>
        @csrf
    </form>
@endsection
