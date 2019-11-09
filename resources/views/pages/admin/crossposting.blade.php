@extends('layouts.admin')
@section('admin_content')
    <div class="admin-panel__heading-container">
        <div class="admin-panel__heading">Кросспостинг</div>
    </div>
    <div class="admin-panel__main-content">
        <div class="admin-panel__crossposting">
            @foreach ($services as $service)
            <div class="admin-panel__crossposting__service">
                <div class="admin-panel__crossposting__top">
                    <div class="admin-panel__crossposting__top__left">
                        <p class="admin-panel__crossposting__name">{{$service['name']}}</p>
                        <p class="admin-panel__crossposting___status">Статус: <strong>{{$service['is_active'] ? "Активен" : "Не активен"}}</strong></p>
                    </div>
                    <div class="admin-panel__crossposting__top__right">
                        @if ($service['can_auto_connect'])<a target="_blank" href="{{route('crosspostAutoconnect', $service['id'])}}" class="button button--light">Подключить</a>@endif
                    </div>
                </div>
                @if (view()->exists('blocks.crossposting.instructions.'.$service['id']))
                <div class="admin-panel__crossposting__instructions">
                    @include('blocks/crossposting/instructions/'.$service['id'], $service)
                </div>
                @endif
                <form method="POST" action="{{route('crosspostSaveSettings', $service['id'])}}" class="form admin-panel__crossposting__form">
                    <div class="response"></div>
                    @if (view()->exists('blocks.crossposting.forms.'.$service['id']))
                        @include('blocks/crossposting/forms/'.$service['id'], $service)
                    @else
                        @foreach ($service['settings'] as $setting)
                            @if (!isset($setting['visible']) || $setting['visible'] === true)
                                <div class="input-container">
                                    <label class="input-container__label">{{$setting['name']}}</label>
                                    <div class="input-container__inner">
                                        <input class="input" name="{{$setting['id']}}" value="{{$setting['value']}}"/>
                                        <span class="input-container__message"></span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                    <button class="button button--light">Сохранить</button>
                 </form>
            </div>
            @endforeach
        </div>
    </div>


@endsection