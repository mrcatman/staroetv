@if ($record->telegram_id && count($record->all_telegram_sources) > 1)
<div class="tabs" data-id="parts">
    @for ($i = 0; $i < count($record->all_telegram_sources); $i++)
    <a class="tab @if ($i == 0) tab--active @endif" data-content="part_{{$i}}">Часть {{$i + 1}}</a>
    @endfor
</div>
@endif
@if ($record->is_radio)
    @if ($record->use_own_player)
        <audio @if (isset($autoplay) && $autoplay) autoplay="autoplay" @endif  data-title="{{$record->title}}" data-url="{{$record->url}}"  data-id="{{$record->id}}" class="own-player own-player--radio" controls>
            <source src="{{$record->source_path}}">
        </audio>
    @else
        {!! $record->embed_code !!}
    @endif
@else
    @if ($record->use_own_player || $record->telegram_id)
        @if ($record->telegram_id && count($record->all_telegram_sources) > 1)
            @for ($i = 0; $i < count($record->all_telegram_sources); $i++)
            <div class="tab-content" data-id="parts" data-tab="part_{{$i}}" @if($i != 0) style="display: none" @endif>
                <video @if (isset($autoplay) && $autoplay) autoplay="autoplay" @endif data-title="{{$record->title}} (часть {{$i}}" data-url="{{$record->url}}#part_{{$i}}" data-id="{{$record->id}}" poster="{{$record->all_telegram_thumbs[$i]}}"  class="own-player" controls>
                    <source src="{{$record->all_telegram_sources[$i]}}" type="video/mp4">
                </video>
            </div>
            @endfor
        @else
            <video @if (isset($autoplay) && $autoplay) autoplay="autoplay" @endif data-title="{{$record->title}}" data-url="{{$record->url}}" data-id="{{$record->id}}" poster="{{$record->cover}}?{{$record->updated_at}}"  class="own-player" controls>
                <source src="{{$record->source_path ? $record->source_hls : $record->source_telegram}}" type="{{$record->source_path ? 'application/vnd.apple.mpegurl' : 'video/mp4' }}">
            </video>
        @endif
    @elseif (strpos($record->embed_code, "youtu") !== false && !request()->has('original_player') && false)
        <div class="plyr__video-embed own-player" data-title="{{$record->title}}" data-url="{{$record->url}}" data-id="{{$record->id}}" >
            <iframe
                src="https://www.youtube.com/embed/{{$record->embed_youtube_id}}?origin=https://plyr.io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
                allowfullscreen
                allowtransparency
                allow="autoplay"
            ></iframe>
        </div>
    @else
        <div class="record-page__player-container">
            {!! $record->embed_code !!}
        </div>
    @endif
@endif
