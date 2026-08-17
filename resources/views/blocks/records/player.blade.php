@if ($record->sources_count > 1)
    <div class="box">
        <div class="box__inner">
            <div class="tabs" data-id="parts">
                @for ($i = 0; $i < $record->sources_count; $i++)
                    <a class="tab @if ($i == 0) tab--active @endif" data-content="part_{{$i}}">Часть {{$i + 1}}</a>
                @endfor
            </div>
        </div>
    </div>
@endif
@if ($record->is_radio)
    @if ($record->use_own_player)
        <audio @if (isset($autoplay) && $autoplay) autoplay="autoplay" @endif  data-title="{{$record->title}}"
               data-url="{{config('app.url')}}{{$record->url}}" data-id="{{$record->id}}"
               class="own-player own-player--radio" controls>
            <source src="{{$record->source_audio}}">
        </audio>
    @else
        {!! $record->embed_code !!}
    @endif
@else
    @if (($record->use_own_player) || $record->telegram_id)
        @if ($record->telegram_id && count($record->all_telegram_sources) > 1)
            @for ($i = 0; $i < count($record->all_telegram_sources); $i++)
                <div class="tab-content" data-id="parts" data-tab="part_{{$i}}"
                     @if($i != 0) style="display: none" @endif>
                    <video @if (isset($autoplay) && $autoplay) autoplay="autoplay"
                           @endif data-title="{{$record->title}} (часть {{$i}}"
                           data-url="{{config('app.url')}}{{$record->url}}#part_{{$i}}"
                           data-id="{{$record->id}}" poster="{{$record->all_telegram_thumbs[$i]}}" class="own-player"
                           controls>
                        <source src="{{$record->all_telegram_sources[$i]}}" type="video/mp4">
                    </video>
                </div>
            @endfor
        @else
            <video @if (isset($autoplay) && $autoplay) autoplay="autoplay" @endif data-title="{{$record->title}}"
                   data-url="{{config('app.url')}}{{$record->url}}" data-id="{{$record->id}}"
                   poster="{{$record->cover}}?{{$record->updated_at->getTimestamp()}}" class="own-player" controls>

                @if ($record->source_path)
                    @if ($record->use_webm)
                        <source src="{{$record->source_webm}}" type="video/webm"/>
                    @else
                        <source src="{{$record->source_hls}}" type="application/vnd.apple.mpegurl"/>
                    @endif
                @else
                    <source src="{{$record->source_telegram}}" type="video/mp4">
                @endif
            </video>
        @endif
    @else
        <div class="record-page__player-container">
            @if ($record->multiple_embeds)
                @for ($i = 0; $i < count($record->multiple_embeds); $i++)
                    <div class="tab-content" data-id="parts" data-tab="part_{{$i}}"
                         @if($i != 0) style="display: none" @endif>
                        {!! $record->multiple_embeds[$i] !!}
                    </div>
                @endfor
            @else
                {!! $record->embed_code !!}
            @endif
            @if(strpos($record->embed_code, "youtu") !== false)
                <div class="record-page__download-overlay" data-id="{{$record->id}}" data-title="{{$record->title}}"
                     data-url="{{$record->url}}" data-poster="{{$record->cover}}" style="display: none">
                    <div class="record-page__download-overlay__background"
                         style="background-image: url('{{$record->cover}}')"></div>
                    <a class="record-page__download-overlay__button">
                        <i class="fa fa-play"></i>
                    </a>
                    <div style="display: none" class="record-page__download-overlay__error">
                        <div class="record-page__download-overlay__error__heading">
                            Ошибка загрузки видео
                        </div>
                        Уже работаем над решением проблемы
                    </div>
                </div>
            @endif
        </div>
    @endif
@endif

@if(strpos($record->embed_code, "youtu") !== false && !$record->use_own_player && !$record->telegram_id)
    <div class="warning-alert record-page__youtube-alert">Возможны проблемы с загрузкой этого видео, если у вас не
        работает Youtube (вы знаете, что делать)
    </div>
@endif
