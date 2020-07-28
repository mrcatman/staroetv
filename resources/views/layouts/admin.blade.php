@extends('layouts.default')
@section('container-class') container--admin-panel @endsection
@section('content')
    <div class="admin-panel">
        @include('blocks/admin_sidebar')
        <div class="admin-panel__content">
            @yield('admin_content')
        </div>

    </div>

@endsection
