<a href="{{$channel->full_url}}" class="channel-item" data-city="{{$channel->city}}" data-country="{{$channel->country}}">
    <div class="channel-item__logo channel-item__logo--back" @if ($channel->logo) style="background-image:url({{$channel->logo->url}})"  @endif></div>
    <div class="channel-item__logo" @if ($channel->logo) style="background-image:url({{$channel->logo->url}})"  @endif></div>
    <span class="channel-item__name" >{{$channel->name}}</span>
</a>