@extends('layouts.admin')
@section('admin_content')
    <program-categories-manager :categories='{!! str_replace("'","&#39;",json_encode($program_categories, JSON_HEX_QUOT)) !!}' />
@endsection
