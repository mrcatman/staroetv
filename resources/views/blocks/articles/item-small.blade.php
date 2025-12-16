<a href="{{$article->url}}" class="short-article">
    <div class="short-article__top">
        @if ($article->cover_url != "")
            <div class="short-article__cover" style="background-image:url({{$article->cover_url}})"></div>
        @endif
        <div class="short-article__texts">
            <div class="short-article__date"><i class="fa fa-calendar"></i>{{$article->created_at}}</div>
            <div class="short-article__title">
                {{ $article->title }}
            </div>
            <div class="short-article__info">
                <span class="short-article__views"><i class="fa fa-eye"></i>{{$article->views}}</span>
                <span class="short-article__comments"><i class="fa fa-comment"></i>{{$article->comments_count}}</span>
            </div>
        </div>

    </div>

</a>
