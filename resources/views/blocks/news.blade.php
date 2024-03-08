<div class="news-block @if ((!isset($show_cover) || $show_cover) && $news_item->cover_url != "") news-block--with-picture @elseif ($show_cover) news-block--no-picture @endif @if(isset($class)) {{$class}} @endif @if ($news_item->pending) news-block--pending @endif">
    @if (!isset($show_cover) || $show_cover)
        @if ($news_item->cover_url != "")
        <a href="{{$news_item->url}}" class="news-block__cover" style="background-image:url({{$news_item->cover_url}})"></a>
        @endif
    @endif
    <div class="news-block__texts">
        @if (!isset($hide_tags) || !$hide_tags)
        <div class="tags-list">
            @foreach ($news_item->tags as $tag)
            <a href="/articles?tag={{$tag->url}}" class="tags-list__item">{{$tag->name}}</a>
            @endforeach
        </div>
        @endif
        <a href="{{$news_item->url}}" class="news-block__title">{{$news_item->title}}</a>
        <a href="{{$news_item->url}}" class="news-block__time">{{$news_item->created_at}}</a>
        <a href="{{$news_item->url}}" class="news-block__short-content">
            {!! $news_item->short_content !!}
        </a>
    </div>

</div>
