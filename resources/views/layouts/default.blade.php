@php($pjax = request()->header('X-PJAX', false) || request()->input('X-PJAX', false))
@if (!$pjax)
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    @endif
    @hasSection('page-title')
    <title>@yield('page-title') - Старый Телевизор</title>
    @else
    <title>Старый Телевизор</title>
    @endif

    @if (!$pjax)
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @routes

    @yield('head')

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <meta name="google-site-verification" content="hzQA7v3s7GcLa45qSrEmM-tDrjNRl8K0bspcnBencP0" />
    <meta name="yandex-verification" content="844947ab3de2442b" />
</head>
<body @if(request()->cookie('theme-dark', 0) == 1) class="theme-dark" @else class="theme-light" @endif>
    <div class="main">
        @include('blocks.global.header')
        <div class="content">
            <div id="app" class="content">
                <div class="container inner-page @yield('container-class')" id="pjax-container">
                    @endif

                    <div id="pjax-content" data-vue="{{isset($vue) && $vue ? 1 : 0}}">
                        @yield('content')
                    </div>

                    @if (!$pjax)
                </div>
            </div>
        </div>

        @include('blocks.global.footer')
    </div>

    @guest
        @include('blocks.auth.login-modal')
    @endguest
</body>

<script src="https://yastatic.net/share2/share.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render=6LccwdUZAAAAANbvD4YOUIKQXR77BP8Zg5A-a9UT"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
<script async defer src="https://telegram.org/js/telegram-widget.js?22"></script>
@endif
@if (!$pjax)
@yield('scripts')
@else
<div data-script="@yield('scripts')" id="pjax_scripts_container"></div>
@endif
@if (!$pjax)
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
</html>
@endif
