@extends('layouts.admin')
@section('admin_content')
    <smiles-manager :smiles='{!! str_replace("'","&#39;",json_encode($smiles, JSON_HEX_QUOT)) !!}' />
@endsection
