<a title="{{$channel->name}}" href="{{$channel->full_url}}" class="channel-item @if ($channel->pending) channel-item--pending @endif" data-city="{{$channel->city}}" @if (isset($region)) data-region="{{$region}}" @endif data-country="{{$channel->country}}">
 <!--   <div class="channel-item__background" style="background: {{$channel->background}}"></div> -->
    <div class="channel-item__logo" @if ($channel->logo_url) style="background-image:url({{$channel->logo_url}})"  @endif></div>
    <span class="channel-item__name" >{{$channel->name}}</span>
</a>
