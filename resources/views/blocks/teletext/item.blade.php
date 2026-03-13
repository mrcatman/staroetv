<a href="{{$teletext->url}}" class="record-item teletext-item @if ($teletext->pending) record-item--pending @endif">
    <div class="record-item__cover teletext-item__cover" style="background-image: url('{{$teletext->cover}}')">
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
    </div>
</a>
