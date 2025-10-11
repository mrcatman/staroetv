<div class="box box--dark">
    <div class="box__inner">
        <div class="records-list__filters">

            <div class="top-list records-list__years">
                <a class="top-list__item top-list__item--all @if (!$selected_year) top-list__item--active @endif"
                   href="{{request()->url()}}">
                    <span class="top-list__item__name">Все годы</span>
                </a>
                @foreach ($years as $year => $count)
                    <a class="top-list__item @if ($selected_year == $year) top-list__item--active @endif"
                       href="{{request()->url()}}?year={{$year}}">
                        <span class="top-list__item__name">{{$year}}</span>
                        <span class="top-list__item__count">{{$count}}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="records-list  records-list--thumbs">
            @foreach($items as $teletext)
                @include('blocks/teletext')
            @endforeach
        </div>
    </div>
    <div class="box__pager">{{$items->links()}}</div>
</div>
