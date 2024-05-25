<a href="{{$record->url}}" class="record-item @if ($record->pending) record-item--pending @endif">
    <div class="record-item__cover" style="background-image: url('{{$record->cover}}')"></div>
    <div class="record-item__texts">
        <span class="record-item__title">
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
        <div class="record-item__info">
            <span class="record-item__date"><i class="fa fa-calendar"></i>{{$record->created_at}}</span>
            <span class="record-item__views"><i class="fa fa-eye"></i>{{$record->views}}</span>
            <span class="record-item__comments"><i class="fa fa-comment"></i>{{count($record->comments)}}</span>
            <div class="record-item__tags" >
                @if ($record->is_advertising)
                    <span class="record-item__tag">Рекламный ролик</span>
                @endif
                @if ($record->is_interprogram && $record->interprogram_name != "")
                    <span class="record-item__tag">{{$record->interprogram_name}}</span>
                @endif
            </div>
        </div>
    </div>
</a>
