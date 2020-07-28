@extends('layouts.default')
@section('content')
    <form class="form box" method="POST">
     <div class="box__heading">
            {{ $event ? "Редактировать событие: ".$event->title : "Добавить событие" }}
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Название<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <input class="input" name="title" value="{{$event ? $event->title : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">URL</label>
                <div class="input-container__inner">
                    <input class="input" name="url" value="{{$event ? $event->url : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Краткое описание</label>
                <div class="input-container__inner">
                    <input class="input" name="short_description" value="{{$event ? $event->short_description : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Описание</label>
                <div class="input-container__inner">
                    <textarea id="editor"  class="input input--textarea" name="description">{{$event ? $event->description : ""}}</textarea>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Обложка</label>
                <div class="input-container__inner">
                    <picture-uploader tag="history_event" name="cover_id" :data="{{$event && $event->coverPicture ? $event->coverPicture : "null"}}"></picture-uploader>
                    <span class="input-container__message"></span>
                </div>
            </div>

            <history-event-editor :data="{{$event ? $event : "null"}}"></history-event-editor>
        </div>
        <div class="box__inner">

            <button class="button">Сохранить</button>
        </div>
        @csrf
    </form>
@endsection
