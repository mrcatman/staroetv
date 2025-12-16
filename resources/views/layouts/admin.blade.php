@extends('layouts.default')
@section('title') @yield('admin-title') @endsection
@section('container-class') container--admin-panel @endsection
@section('content')
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                @yield('admin-title')
            </div>
        </div>
        <div class="box__inner">
            <div class="row row--align-start">
                @include('blocks.admin.sidebar')
                <div class="admin-panel__content">
                    @yield('admin-content')
                </div>
            </div>

        </div>


    </div>

@endsection
