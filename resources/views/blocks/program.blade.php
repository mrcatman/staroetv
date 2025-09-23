@php($url = isset($url) ? $url : $program->full_url)
<div
   class="program @if ($program->pending) program--pending @endif">
    <a href="{{$url}}" class="program__cover">
        <div class="program__cover__foreground" style="background-image: url({{$program->cover_url}})"></div>
        <div class="program__cover__background" style="background-image: url({{$program->cover_url}})"></div>
    </a>
    <a href="{{$url}}" class="program__name">{{$program->name}}</a>
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
