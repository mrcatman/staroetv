@extends('layouts.admin', ['vue' => true])
@section('admin-title')
    Каналы и радио
@endsection
@section('admin-content')
    <channels-manager :channels='@json($channels)' />
@endsection
