@extends('layouts.default')
@section('content')
    <form class="form box" method="POST">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="{{$channel->is_radio ? "/radio" : "/video"}}">Архив</a>
            <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
            @if ($program)
            <a class="breadcrumbs__item" href="{{$program->full_url}}">{{$program->name}}</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">Редактировать</a>
            @else
            <a class="breadcrumbs__item breadcrumbs__item--current">Новая программа</a>
            @endif
        </div>
        <div class="box__heading">
            {{ ($program ? "Редактировать программу: ".$program->name : "Добавить программу") }}
        </div>
         <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Название<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <input class="input" name="name" id="channel_name" value="{{$program ? $program->name : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Короткий URL</label>
                <div class="input-container__inner">
                    <input class="input" name="url" id="channel_url" value="{{$program ? $program->url : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Описание</label>
                <div class="input-container__inner">
                    <textarea id="editor" class="input input--textarea" name="description">{{$program ? $program->description : ""}}</textarea>
                    <span class="input-container__message"></span>
                </div>
            </div>
             <div class="input-container">
                 <label class="input-container__label">Жанр</label>
                 <div class="input-container__inner">
                     <select class="select-classic" name="genre_id">
                         @foreach(\App\Genre::all() as $genre)
                             <option value="{{$genre->id}}">{{$genre->name}}</option>
                         @endforeach
                     </select>
                     <span class="input-container__message"></span>
                 </div>
             </div>
            <div class="input-container">
                <label class="input-container__label">Логотип</label>
                <div class="input-container__inner">
                    <picture-uploader type="logo" name="cover_id" :data="{{$program && $program->coverPicture ? $program->coverPicture : "null"}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
             <div class="row">
                 <div class="col">
                     <div class="input-container input-container--vertical">
                         <label class="input-container__label">Дата первого эфира</label>
                         <div class="input-container__inner">
                             <Datepicker name="date_of_start" value="{{$program ? $program->date_of_start : ''}}"></Datepicker>
                             <span class="input-container__message"></span>
                         </div>
                     </div>
                 </div>
                 <div class="col">
                     <div class="input-container input-container--vertical">
                         <label class="input-container__label">Дата последнего эфира</label>
                         <div class="input-container__inner">
                             <Datepicker can_be_now="true" name="date_of_closedown" value="{{$program ? $program->date_of_start : ''}}"></Datepicker>
                             <span class="input-container__message"></span>
                         </div>
                     </div>
                 </div>
             </div>
            <button class="button">Сохранить</button>
        </div>
        @csrf
    </form>
    @if ($program)
    <form class="form box" action="/programs/merge" method="POST">
        <div class="box__heading">
            Объединить программы
        </div>
        <div class="box__inner">
            <input value="{{$program->id}}" type="hidden" name="original_id" />
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Выберите программу</label>
                <select class="select-classic" name="merged_id">
                    @foreach ($all_programs as $program)
                    <option value="{{$program->id}}">{{$program->name}}</option>
                    @endforeach
                </select>
                <span class="input-container__message"></span>
            </div>
            <button class="button">Объединить</button>
        </div>
        @csrf
    </form>
    @endif
@endsection
