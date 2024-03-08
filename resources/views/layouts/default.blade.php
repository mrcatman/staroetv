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
    @if (auth()->user() && auth()->user()->id == 3358)
        <link rel="stylesheet" href="{{ mix('/css/app.css') }}?{{time()}}">
    @else
        <link rel="stylesheet" href="/css/app.css?v=20122023-2">
    @endif
    <link rel="icon" href="/favicon.ico?1" type="image/x-icon" />
    <meta name="google-site-verification" content="hzQA7v3s7GcLa45qSrEmM-tDrjNRl8K0bspcnBencP0" />
    <meta name="yandex-verification" content="844947ab3de2442b" />
</head>
<body @if(request()->cookie('theme-dark', 0) == 1) class="theme-dark" @else class="theme-light" @endif>
    <div class="main">
        @include('blocks/header')
        <div class="content">
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
        </div>
    </div>
</body>
@guest
    @include('blocks/login_form')
@endguest
@include('blocks/survey_form')
<script src="https://yastatic.net/share2/share.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render=6LccwdUZAAAAANbvD4YOUIKQXR77BP8Zg5A-a9UT"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
@if (auth()->user() && auth()->user()->id == 3358)
<script type="text/javascript" rel="script" src="/js/app.js?{{time()}}"></script>
@else
    <script type="text/javascript" rel="script" src="/js/app.js?v=6062023-1"></script>
@endif
<script src="//cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
<script>
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
</script>
@endif
@if (!request()->header('X-PJAX', false) && !request()->input('X-PJAX', false))
@yield('scripts')
@else
<div data-script="@yield('scripts')" id="pjax_scripts_container"></div>
@endif
@if (!request()->header('X-PJAX', false) && !request()->input('X-PJAX', false))
    @if (auth()->user() && auth()->user()->id == 3358)

    @else
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(4495546, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            webvisor:true
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/4495546" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
    @endif
</html>
@endif
