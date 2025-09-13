@extends('layouts.default', ['vue' => true])
@section('content')
    <form class="box box--top" method="POST">
        <div class="box__heading">
            <div class="box__heading__inner">
                {{$data['is_radio'] ? ($record ? "Редактировать радиозапись" : "Добавить радиозапись") :  ($record ? "Редактировать видео" : "Добавить видео")}}
            </div>
            <div class="box__heading__right">
                <a href="{{$record ? $record->url : 'Назад'}}" class="button button--light">Назад</a>
            </div>
        </div>
        <div class="box__inner">
            <record-form upload-endpoint="{{$upload_endpoint}}" :can-upload="{{$can_upload ? "true" : "false"}}" :can-edit-all="{{$can_edit_all ? "true" : "false"}}" :meta='@json($data)' :channels='@json($channels)' :record='@json($record)'></record-form>
        </div>
        @csrf
    </form>
@endsection
