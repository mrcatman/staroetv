<div class="awards-history">
@foreach ($awards as $award)
    <div class="awards-history__item">
        <img class="awards-history__item__picture" src="{{$award->award->picture->url}}"/>
            <div class="awards-history__item__texts">
            <div class="awards-history__item__top">
                <a @if ($award->from) target="_blank" href="{{$award->from->url}}" @endif class="awards-history__item__user">{{$award->from ? $award->from->username : "DELETED"}}</a>
                <span class="awards-history__item__date">{{$award->created_at}}</span>
            </div>
            <div class="awards-history__item__comment">
               {{$award->comment}}
            </div>
        </div>
    </div>
@endforeach
</div>