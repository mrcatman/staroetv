@extends('layouts.admin', ['vue' => true])
@section('admin-title')
    Пользователи
@endsection
@section('admin-content')
    <users-manager :groups="{{$groups}}" />
@endsection
