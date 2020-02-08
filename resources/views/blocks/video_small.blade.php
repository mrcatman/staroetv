<a href="{{$video->url}}" class="small-video">
    <div class="small-video__cover" style="background-image: url({{$video->cover}})"></div>
    <div class="small-video__texts">
        <span class="small-video__title">
            @if (isset($highlight) && $highlight)
                {!! \App\Helpers\HighlightHelper::highlight($video->title, $highlight) !!}
            @else
                {!! $record->title !!}
            @endif
        </span>
    </div>
</a>
