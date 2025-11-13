@if (!$only_list)
@if (count($notifications) === 0)
    <div class="notifications__empty">У вас нет уведомлений</div>
@endif
<div class="notifications__items">
@endif
    @foreach ($notifications as $notification)
        <a href="{{$notification->link}}" class="notification">
            <div class="notification__inner">

                <div class="notification__text">
                    {!! $notification->text !!}
                    <div class="notification__time">{{$notification->time}}</div>
                </div>
                @if ($notification->picture)
                    <div class="notification__picture" style="background-image:url({{$notification->picture}})"></div>
                @endif
            </div>

        </a>
    @endforeach
@if (!$only_list)
</div>
@if ($show_more)
<a class="button notifications__more">Показать ещё</a>
@endif
@endif
