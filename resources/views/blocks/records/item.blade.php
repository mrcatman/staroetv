@php($full_title =
    isset($highlight) && $highlight ?
    \App\Helpers\HighlightHelper::highlight($record->title, $highlight) :

    (isset($title) && $title ?
        $title :
        ((isset($new_titles) && $new_titles) ?
        $record->parsed_short_description :
        $record->title)
    )
)

@php($url = isset($url) ? $url : $record->url ?? $record->full_url)
@php($hide_info = isset($hide_info) ? $hide_info : false)
<a data-id={{$record->id}} href="{{$url}}" class="record-item @if ($record->pending) record-item--pending @endif @if ($record->use_own_player) record-item--with-preview @endif"
    @if ($record->use_own_player) data-src="{{$record->download_url}}" @endif
>
    <div class="record-item__cover" style="background-image: url('{{$record->cover ?? $record->cover_url}}')">
        @if ($record->length)
            <div class="record-item__duration">{{$record->formatted_duration}}</div>
        @endif
    </div>

    <div class="record-item__texts">
        <span class="record-item__title">{!! $full_title !!}</span>
        @if (isset($new_titles) && $new_titles)
            @if ($record->broadcast_date != '')
                <span class="record-item__broadcast-date">
                <i class="fa fa-clock"></i>
                {{ $record->broadcast_date }}
            </span>
            @endif
        @endif
        @if ($record->description_topics)
            <div class="record-item__description">
                @foreach($record->description_topics as $topic)
                    <div title="{{$topic}}" class="record-item__description__topic">
                        {{$topic}}
                    </div>
                @endforeach
            </div>
        @endif
        @if (!$hide_info)
            <div class="record-item__info">
                <span class="record-item__date"><i class="fa fa-calendar"></i>{{$record->created_at}}</span>
                <span class="record-item__views"><i class="fa fa-eye"></i>{{$record->views}}</span>
                <span class="record-item__comments"><i class="fa fa-comment"></i>{{count($record->comments)}}</span>
                <div class="record-item__tags">
                    @if ($record->is_advertising)
                        <span class="record-item__tag">Рекламный ролик</span>
                    @endif
                    @if ($record->is_interprogram && $record->interprogram_name != "")
                        <span class="record-item__tag">{{$record->interprogram_name}}</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</a>
