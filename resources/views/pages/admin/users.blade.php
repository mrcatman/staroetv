@extends('layouts.admin')
@section('admin_content')
    <users-manager :groups="{{$groups}}" :users="{{$users}}" />
@endsection
