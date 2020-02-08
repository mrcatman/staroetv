@if (!request()->header('X-PJAX', false) && !request()->input('X-PJAX', false))
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    @hasSection('page-title')
    <title>@yield('page-title') - Старый Телевизор</title>
    @else
    <title>Старый Телевизор</title>
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('head')
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
</head>
<body>
    @include('blocks/header')
    <div id="app" class="content">
        @endif
        @hasSection('page-title')
            <title>@yield('page-title') - Старый Телевизор</title>
        @else
            <title>Старый Телевизор</title>
        @endif
        <div class="container @yield('container-class')" id="pjax-container">
            @yield('content')
        </div>
        @if (!request()->header('X-PJAX', false) && !request()->input('X-PJAX', false))
    </div>
    @include('blocks/footer')
</body>
@guest
    @include('blocks/login_form')
@endguest

<script type="text/javascript" rel="script" src="{{asset('js/app.js')}}"></script>
<script src="//cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@endif
@if (!request()->header('X-PJAX', false) && !request()->input('X-PJAX', false))
@yield('scripts')
@else
<div data-script="@yield('scripts')" id="pjax_scripts_container"></div>
@endif
@if (!request()->header('X-PJAX', false) && !request()->input('X-PJAX', false))
</html>
@endif
