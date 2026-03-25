<a href="{{$teletext->url}}" class="record-item teletext-item @if ($teletext->pending) record-item--pending @endif">
    <div class="record-item__cover teletext-item__cover">
        <div class="record-item__cover__picture" style="background-image: url('{{$teletext->cover}}')"></div>
        @if ($teletext->quality)
        <div class="record-item__stars">
            <div class="record-item__stars__inner">
                <div class="record-item__stars__list record-item__stars__list--percent" style="width: {{$teletext->quality / 10 * 100}}%">
                    <i class="fa fa-star"></i>  <i class="fa fa-star"></i>  <i class="fa fa-star"></i>  <i class="fa fa-star"></i>  <i class="fa fa-star"></i>
                </div>
                <div class="record-item__stars__list">
                    <i class="fa fa-star"></i>  <i class="fa fa-star"></i>  <i class="fa fa-star"></i>  <i class="fa fa-star"></i>  <i class="fa fa-star"></i>
                </div>
            </div>


        </div>
        @endif
    </div>
    <div class="record-item__texts">
        <span class="record-item__title">
            @if ($teletext->channel_logo)
            <img class="record-item__title__logo" src="{{$teletext->channel_logo}}" />
            @endif
            {{$teletext->date_formatted}}
        </span>
        <div class="record-item__info">
            <span class="record-item__date"><i class="fa fa-calendar"></i>{{$teletext->created_at}}</span>
            <span class="record-item__views"><i class="fa fa-eye"></i>{{$teletext->views}}</span>
            @if (count($teletext->comments) > 0)<span class="record-item__comments"><i class="fa fa-comment"></i>{{count($teletext->comments)}}</span>@endif
        </div>
    </div>
</a>
