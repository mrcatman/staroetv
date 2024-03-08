<a href="{{$record->url}}" class="radio-recording @if ($record->pending) radio-recording--pending @endif">
    <div class="radio-recording__button">
        <i class="fa fa-play"></i>
    </div>
    <div class="radio-recording__texts">
        <span class="radio-recording__title">
            @if (isset($highlight) && $highlight)
                {!! \App\Helpers\HighlightHelper::highlight($record->title, $highlight) !!}
            @else
                @if (isset($title) && $title)
                    {!!  $title !!}
                @else
                {!!  $record->title  !!}
                @endif
            @endif
        </span>
        <div class="radio-recording__info">
            <span class="radio-recording__date"><i class="fa fa-calendar"></i>{{$record->created_at}}</span>
            <span class="radio-recording__listens"><i class="fa fa-headphones-alt"></i>{{$record->views}}</span>
            <span class="radio-recording__comments"><i class="fa fa-comment"></i>{{count($record->comments)}}</span>
        </div>
    </div>
</a>
