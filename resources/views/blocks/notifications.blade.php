@if (count($notifications) === 0)
<div class="notifications__empty">У вас нет уведомлений</div>
@endif
@foreach ($notifications as $notification)
    <a href="{{$notification->link}}" class="notification">
        <div class="notification__inner">
            @if ($notification->picture)
                <div class="notification__picture" style="background-image:url({{$notification->picture}})"></div>
            @endif
            <div class="notification__text">
                {!! $notification->text !!}
            </div>
        </div>
        <div class="notification__time">{{$notification->time}}</div>
    </a>
@endforeach
