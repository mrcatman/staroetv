@if (count($results) === 0)
    <div class="site-search__nothing-found">
        <div class="site-search__nothing-found__inner">
            По запросу <strong>"{{$search}}"</strong> ничего не найдено
        </div>
    </div>
@endif
@foreach ($results as $section)
<div class="site-search__section site-search__section--{{$section['name']}}">
    <a @if (isset($section['url'])) target="_blank" href="{{$section['url']}}" @endif class="site-search__section__title  @if (isset($section['url'])) site-search__section__title--link @endif">
        {{$section['title']}}
        <span class="site-search__section__title__count">
            {{$section['count']}}
        </span>
        @if (isset($section['url']))
        <span class="site-search__section__title__link-icon">
            <i class="fa fa-chevron-right"></i>
        </span>
       @endif
    </a>
    <div class="site-search__section__results">
        @foreach ($section['list'] as $item)
        <a target="_blank"  href="{{$item['url']}}" class="site-search__result">
            @if (isset($item['picture']) && $item['picture'] != '')
            <div class="site-search__result__cover" style="background-image:url({{$item['picture']}})"></div>
            @endif
            <div class="site-search__result__texts">
                <div class="site-search__result__title">{!! \App\Helpers\HighlightHelper::highlight($item['title'], $search) !!}</div>
                @if (isset($item['description']) && $item['description'] != '')
                <div class="site-search__result__description">{!! $item['description'] !!}</div>
                @endif
                @if (isset($item['additional']) && $item['additional'] != '')
                <div class="site-search__result__additional">{{$item['additional']}}</div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
@endforeach
