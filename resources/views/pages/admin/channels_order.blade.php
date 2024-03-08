@extends('layouts.admin')
@section('admin_content')
    <channels-order-manager :channels='@json($channels)' />
@endsection
