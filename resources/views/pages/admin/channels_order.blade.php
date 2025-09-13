@extends('layouts.admin' , ['vue' => true])
@section('admin_content')
    <channels-order-manager :channels='@json($channels)' />
@endsection
