@extends('layouts.admin')
@section('admin_content')
    <channels-manager :channels='{!! json_encode($channels) !!}' />
@endsection
