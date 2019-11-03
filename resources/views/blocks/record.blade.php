<a href="{{$record->url}}" class="record-item">
    <div class="record-item__cover" style="background-image: url({{$record->cover}})"></div>
    <div class="record-item__texts">
        <span class="record-item__title">
            @if (isset($highlight) && $highlight)
                {!! \App\Helpers\HighlightHelper::highlight($record->title, $highlight) !!}
            @else
                {!! $record->title !!}
            @endif
        </span>
        <div class="record-item__info">
            <span class="record-item__date"><i class="fa fa-calendar"></i>{{$record->created_at}}</span>
            <span class="record-item__views"><i class="fa fa-eye"></i>{{$record->views}}</span>
            <span class="record-item__comments"><i class="fa fa-comment"></i>{{count($record->comments)}}</span>
        </div>
    </div>
</a>
