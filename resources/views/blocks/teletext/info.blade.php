<div class="icon-blocks">
    @if ($teletext->channel)
        <a href="{{$teletext->channel->full_url}}" class="icon-block">
            <div class="icon-block__picture" style="background-image: url({{$teletext->channel_logo}})"></div>
            <span class="icon-block__text">{{$teletext->channel_name}}</span>
        </a>
    @endif
        @if ($teletext->user)
        <a href="{{$teletext->user->url}}" class="icon-block">
            <i class="fa fa-user"></i>
            <span class="icon-block__text">{{$teletext->user->username}}</span>
        </a>
        @endif
    <span class="icon-block">
        <i class="fa fa-eye"></i>
        <span class="icon-block__text">{{$teletext->views}}</span>
    </span>
    <span class="icon-block">
        <i class="fa fa-clock"></i>
        <span class="icon-block__text">{{\App\Helpers\DatesHelper::format($teletext->created_at)}}</span>
    </span>
</div>
