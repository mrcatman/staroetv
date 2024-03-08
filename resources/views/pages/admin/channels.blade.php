@extends('layouts.admin')
@section('admin_content')
    <channels-manager :channels='@json($channels)' />
@endsection
