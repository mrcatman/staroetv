<div class="icon-blocks">
    @if (!$record->is_advertising && $record->channel)
        <a href="{{$record->channel->full_url}}" class="icon-block">
            <div class="icon-block__picture" style="background-image: url({{$record->getChannelLogo()}})"></div>
            <span class="icon-block__text">{{$record->getChannelName()}}</span>
        </a>
    @endif
    @if ($record->user) <a href="{{$record->user->url}}"
        @else <span @endif class="icon-block">
            <i class="fa fa-user"></i>
            <span class="icon-block__text">{{$record->user ? $record->user->username : $record->author_username}}</span>
        @if ($record->user) </a>
            @else
        </span>
        @endif
    <span class="icon-block">
        @if ($record->is_radio)
            <i class="fa fa-headphones-alt"></i>
        @else
            <i class="fa fa-eye"></i>
        @endif
        <span class="icon-block__text">{{$record->views}}</span>
    </span>
    <span class="icon-block">
        <i class="fa fa-clock"></i>
        <span class="icon-block__text">{{$record->created_at}}</span>
    </span>
    @if ($record->download_url)
        <a class="icon-block" target="_blank" download href="{{$record->download_url}}">
            <i class="fa fa-download"></i>
            <span class="icon-block__text">Скачать</span>
        </a>
    @endif
</div>
