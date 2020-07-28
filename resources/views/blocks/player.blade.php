@if ($record->is_radio)
    @if ($record->use_own_player)
        <audio data-title="{{$record->title}}" data-url="{{$record->url}}"  data-id="{{$record->id}}" class="own-player own-player--radio" controls>
            <source src="{{$record->source_path}}">
        </audio>
    @else
        {!! $record->embed_code !!}
    @endif
@else
    @if ($record->use_own_player)
        <video data-title="{{$record->title}}" data-url="{{$record->url}}" data-id="{{$record->id}}" poster="{{$record->cover}}"  class="own-player" controls>
            <source src="{{$record->source_path}}">
        </video>
    @else
        <div class="record-page__player-container">
            {!! $record->embed_code !!}
        </div>
    @endif
@endif
