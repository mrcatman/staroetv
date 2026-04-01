<a href="{{$article->full_url}}"
   class="article article--big">
    <div class="article__cover"
         style="background-image:url({{$article->cover_url}})"></div>
    <div class="article__texts">
        <div class="icon-blocks">
                                                <span class="icon-block">
                                                    <i class="fa fa-clock"></i>
                                                    <span class="icon-block__text">{{$article->created_at}}</span>
                                                </span>
            <span class="icon-block">
                                                    <i class="fa fa-eye"></i>
                                                    <span class="icon-block__text">{{$article->views}}</span>
                                                </span>
            <span class="icon-block">
                                                    <i class="fa fa-comment"></i>
                                                    <span class="icon-block__text">{{$article->comments_count}}</span>
                                                </span>
        </div>
        <div class="article__title">{{$article->title}}</div>
        <div class="article__short-content">{{$article->short_content}}</div>
    </div>
</a>
