@php($url = isset($url) ? $url : $program->full_url)
@php($is_radio = isset($is_radio) ? $is_radio : false)
<div
   class="program @if ($is_radio) program--radio @endif @if ($program->pending) program--pending @endif">
    @if (!$is_radio)
    <a href="{{$url}}" class="program__cover">
        @if (isset($program->records_count))
            <span class="program__count program__count--cover">{{$program->records_count}}</span>
        @endif
        <div class="program__cover__foreground" style="background-image: url({{$program->cover_url}})"></div>
        <div class="program__cover__background" style="background-image: url({{$program->cover_url}})"></div>
    </a>
    @endif

    <a href="{{$url}}" class="program__name">
        {{$program->name}}
        @if ($is_radio && isset($program->records_count))
            <span class="program__count">{{$program->records_count}}</span>
        @endif
    </a>
    @if (isset($show_channels) && $show_channels)
        <div class="program__channels">
            @foreach ($program->channels_history as $program_channel)
                <a href="{{$program_channel['url']}}" class="program__channel__name">
                    @if ($program_channel['logo'])
                        <img class="program__channel__logo" src="{{$program_channel['logo']}}"/>
                    @endif
                    {{$program_channel['name']}}
                </a>
            @endforeach
        </div>
    @endif
</div>
