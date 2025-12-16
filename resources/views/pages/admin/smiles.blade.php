@extends('layouts.admin', ['vue' => true])
@section('admin-title')
    Смайлы
@endsection
@section('admin-content')
    <smiles-manager :smiles='{!! str_replace("'","&#39;",json_encode($smiles, JSON_HEX_QUOT)) !!}' />
@endsection
