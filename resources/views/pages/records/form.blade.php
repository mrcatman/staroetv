@extends('layouts.default', ['vue' => true])
@section('content')
    <form class="box"
          method="POST">
        <div class="box__heading">
            <div class="box__heading__inner">
                {{$data['is_radio'] ? ($record ? "Редактировать радиозапись" : "Добавить радиозапись") :  ($record ? "Редактировать видео" : "Добавить видео")}}
            </div>
            <div class="box__heading__buttons">
                <a href="{{$record ? $record->url : typed_route('records.[RECORD].index', $data['is_radio'])}}" class="button button--light">Назад</a>
            </div>
        </div>
        <div class="box__inner" data-vue-root>
            <record-form :can-edit-all="{{$can_edit_all ? "true" : "false"}}" :start-params="{is_radio: {{$data['is_radio'] ? "true" : "false"}}}" :record='@json($record)'></record-form>
        </div>
        @csrf
    </form>
@endsection
