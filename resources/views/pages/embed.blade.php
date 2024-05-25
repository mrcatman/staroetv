<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>{{$record->title}} - Старый Телевизор</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
</head>
<body>
<div class="embed-page">
    @include('blocks/player', ['record' => $record])
</div>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
<script type="text/javascript" rel="script" src="{{asset('js/app.js')}}"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
</body>
