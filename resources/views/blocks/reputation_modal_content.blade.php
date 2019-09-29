<div class="reputation-history">
@foreach ($reputation as $reputation_item)
    <div class="reputation-history__item">
        <div class="reputation-history__item__top">
            <a @if ($reputation_item->from) target="_blank" href="{{$reputation_item->from->url}}" @endif class="reputation-history__item__user">{{$reputation_item->from ? $reputation_item->from->username : "DELETED"}}</a>
            <span class="reputation-history__item__weight @if ($reputation_item->weight > 0) reputation-history__item__weight--positive @elseif ($reputation_item->weight < 0) reputation-history__item__weight--negative @else reputation-history__item__weight--neutral @endif">
                    {{$reputation_item->weight > 0 ? "+".$reputation_item->weight  : $reputation_item->weight }}
                </span>
            <span class="reputation-history__item__date">{{$reputation_item->created_at}}</span>
            @if ($reputation_item->link)<a href="{{$reputation_item->link}}" target="_blank" class="reputation-history__item__source">[Источник]</a>@endif
        </div>
        <div class="reputation-history__item__comment">
            {{$reputation_item->comment}}
        </div>
        @if ($reputation_item->reply_comment != "")
            <div class="reputation-history__item__reply-comment">
                <strong>Ответ: </strong>{{$reputation_item->reply_comment}}
            </div>
        @endif
    </div>
@endforeach
</div>