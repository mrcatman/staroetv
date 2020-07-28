@extends('layouts.admin')
@section('admin_content')
    <categories-manager :categories='{!! str_replace("'","&#39;",json_encode($categories, JSON_HEX_QUOT)) !!}' />
@endsection
