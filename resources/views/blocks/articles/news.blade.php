<div class="news-block @if ((!isset($show_cover) || $show_cover) && $news_item->cover_url != "") news-block--with-picture @elseif ($show_cover) news-block--no-picture @endif @if(isset($class)) {{$class}} @endif @if ($news_item->pending) news-block--pending @endif">
    @if (!isset($show_cover) || $show_cover)
        @if ($news_item->cover_url != "")
        <a href="{{$news_item->full_url}}" class="news-block__cover">
            <div class="news-block__cover__background" style="background-image:url({{$news_item->cover_url}})"></div>
            <div class="news-block__cover__foreground" style="background-image:url({{$news_item->cover_url}})"></div>
        </a>
        @endif
    @endif
    <div class="news-block__texts">
        @if (!isset($hide_tags) || !$hide_tags)
        <div class="tags-list">
            @foreach ($news_item->tags_list as $tag)
            <a href="{{route('articles.index', ['tag' => $tag->url])}}" class="tags-list__item">{{$tag->name}}</a>
            @endforeach
        </div>
        @endif
        <a href="{{$news_item->full_url}}" class="news-block__title">{{$news_item->title}}</a>
        <a href="{{$news_item->full_url}}" class="news-block__info icon-blocks">
            <span class="icon-block">
                <i class="fa fa-clock"></i>
                <span class="icon-block__text">{{$news_item->created_at}}</span>
            </span>
            <span class="icon-block">
                <i class="fa fa-eye"></i>
                <span class="icon-block__text">{{$news_item->views}}</span>
            </span>
            <span class="icon-block">
                <i class="fa fa-comment"></i>
                <span class="icon-block__text">{{$news_item->comments_count}}</span>
            </span>
        </a>
        <a href="{{$news_item->full_url}}" class="news-block__short-content">
            {!! $news_item->short_content !!}
        </a>
    </div>

</div>
