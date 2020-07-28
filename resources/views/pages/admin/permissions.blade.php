@extends('layouts.admin')
@section('admin_content')
    <permissions-manager :permissionsvalues='{!! json_encode($permissions_values) !!}' :defaultgroups='{!! json_encode($default_groups) !!}' :permissions='{!! json_encode($permissions) !!}' :groups='{!! json_encode($groups) !!}' />
@endsection
