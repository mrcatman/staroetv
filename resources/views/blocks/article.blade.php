<div class="news">
    <div class="news__content">
        <div class="news__top">
            <a href="{{$article->url}}" class="news__title">{{$article->title}}</a>
            <div class="news__info">
                <span class="news__date"><i class="fa fa-calendar"></i>{{$article->created_at}}</span>
                <span class="news__views"><i class="fa fa-eye"></i>{{$article->views}}</span>
                <span class="news__comments"><i class="fa fa-comment"></i>{{count($article->comments)}}</span>
                <span class="news__author"><a href="{{$article->user->url}}"><i class="fa fa-user"></i>{{$article->username}}</a></span>
            </div>
        </div>
        <div class="news__short-content">
            {!! $article->short_content !!}
            <a class="news__read-more" href="{{$article->url}}">читать далее</a>
        </div>
    </div>
    @if ($article->cover != "")
        <div class="news__cover" style="background-image:url({{$article->cover}})"></div>
    @endif
</div>