@extends('layouts.default')
@section('content')
    <form class="form box" method="POST">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="{{$channel->is_radio ? "/radio" : "/video"}}">Архив</a>
            <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
            <a class="breadcrumbs__item" href="{{$channel->full_url}}/graphics">Графика</a>
            @if ($package)
                <a class="breadcrumbs__item" href="/channels/{{$channel->id}}/graphics#package_{{$package->id}}">{{$package->name != "" ? $package->name : $package->years_range}}</a>
                <a class="breadcrumbs__item breadcrumbs__item--current">Редактировать</a>
            @else
                <a class="breadcrumbs__item breadcrumbs__item--current">Новый пакет оформления</a>
            @endif
        </div>
        <div class="box__heading">
            {{ $package ? "Редактировать пакет оформления: ".$package->years_range : "Добавить пакет оформления для канала ".$channel->name }}
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Название</label>
                <div class="input-container__inner">
                    <input class="input" name="name" id="name" value="{{$package ? $package->name : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Описание</label>
                <div class="input-container__inner">
                    <textarea class="input input--textarea" name="description">{{$package ? $package->description : ""}}</textarea>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Автор (студия, дизайнер и т.д.)</label>
                <div class="input-container__inner">
                    <input class="input" name="author" id="author" value="{{$package ? $package->author : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            @if ($package)
                <records-list-picker :list="{{$package->records}}" :meta="{is_radio: {{$channel->is_radio ? "true" : "false"}}}" :params="{is_interprogram: true, interprogram_package_id: {{$package->id}}, channel_id: {{$channel->id}}}" :unset-params="{is_interprogram: false, interprogram_package_id: null}" :select="{channel_id: {{$channel->id}}}"/>
            @else
                <h2>Сохраните пакет, чтобы начать добавлять записи</h2>
            @endif
        </div>
        <div class="box__inner">
           <div class="row">
               <div class="col">
                   <div class="input-container input-container--vertical">
                       <label class="input-container__label">В эфире с<span class="input-container__required">*</span></label>
                       <div class="input-container__inner">
                           <Datepicker name="date_start" value="{{$package ? $package->date_start : ''}}"></Datepicker>
                           <span class="input-container__message"></span>
                       </div>
                   </div>
               </div>
               <div class="col">
                   <div class="input-container input-container--vertical">
                       <label class="input-container__label">В эфире до<span class="input-container__required">*</span></label>
                       <div class="input-container__inner">
                           <Datepicker name="date_end" value="{{$package ? $package->date_end : ''}}"></Datepicker>
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
