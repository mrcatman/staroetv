@if (count($related) > 0)
    <div class="box">
        <div class="box__heading box__heading--small">
            <div class="box__heading__inner">
                Смотрите также
            </div>
        </div>
        <div class="box__inner">
            <div class="interprogram-page__related">
                <div class="records-list">
                    @foreach ($related as $item)
                        <a href="{{$item->full_url}}" class="record-item">
                            <div class="record-item__cover"
                                 style="background-image: url({{$item->one_cover}})"></div>
                            <div class="record-item__texts">
                                        <span class="record-item__title">
                                            {{$item->name != "" ? $item->name : $item->years_range}}
                                        </span>
                                <div class="record-item__info">
                                    @if ($item->name != "")
                                        <span class="record-item__date">
                                                    <i class="fa fa-calendar"></i>{{$item->years_range}}
                                                 </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
@endif
