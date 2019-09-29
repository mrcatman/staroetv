@extends('layouts.default')
@section('content')
    <div class="admin-panel">
        <permissions-manager :permissionsvalues='{!! json_encode($permissions_values) !!}' :defaultgroups='{!! json_encode($default_groups) !!}' :permissions='{!! json_encode($permissions) !!}' :groups='{!! json_encode($groups) !!}' />
    </div>
@endsection
