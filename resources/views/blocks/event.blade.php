@if (!isset($big))
    @php($big = false)
@endif
<div class="event @if ($big) event--big @endif @if ($event->pending) event--pending @endif">

    @if ($event->coverPicture)
        <a href="{{$event->full_url}}" class="event__cover" style="background-image:url({{$event->coverPicture->url}})"></a>
    @endif
    <div class="event__texts">
        <a href="{{$event->full_url}}" class="event__title">{{$event->title}}</a>
        @if ($event->date)
        <span class="event__date">{{$event->date_formatted}}</span>
        @endif
        @if ($big)
        <div class="event__short-description">
            {{$event->short_description}}
        </div>
        @endif
    </div>
</div>
