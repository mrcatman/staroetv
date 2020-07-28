<div class="warnings-history">
@foreach ($warnings as $warning)
<div class="warnings-history__item">
    <div class="warnings-history__item__top">
        <a @if ($warning->from) target="_blank" href="{{$warning->from->url}}" @endif class="warnings-history__item__user">{{$warning->from ? $warning->from->username : "DELETED"}}</a>
        <span class="warnings-history__item__weight @if ($warning->weight > 0) warnings-history__item__weight--positive @elseif ($warning->weight < 0) warnings-history__item__weight--negative @else warnings-history__item__weight--neutral @endif">
                    {{$warning->weight > 0 ? "+" : "-" }}
                </span>
        <span class="warnings-history__item__date">{{$warning->created_at}}</span>
    </div>
    <div class="warnings-history__item__comment">
        {{$warning->comment}}
    </div>
</div>
@endforeach
</div>