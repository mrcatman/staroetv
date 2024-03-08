@foreach ($programs as $program)
    <div class="program">
        <a href="{{$program->full_url}}" class="program__cover" style="background-image: url({{$program->coverPicture ? $program->coverPicture->url : ''}})"></a>
        <a href="{{$program->full_url}}" class="program__name">{{$program->name}}</a>
        @if (!isset($hide_channels) || !$hide_channels)
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
@endforeach
