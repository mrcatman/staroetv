@extends('layouts.admin' , ['vue' => true])
@section('admin-title')
    Порядок каналов
@endsection
@section('admin-content')
    <channels-order-manager :channels='@json($channels)' />
@endsection
