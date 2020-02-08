@extends('layouts.default')
@section('content')
    <form class="form box" method="POST">
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
