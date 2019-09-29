<div class="news">
    <div class="news__top">
        <a href="{{$article->url}}" class="news__title">{{$article->title}}</a>
        <div class="news__info">
            <span class="news__date"><i class="fa fa-calendar"></i>{{$article->created_at}}</span>
            <span class="news__views"><i class="fa fa-eye"></i>{{$article->views}}</span>
            <span class="news__comments"><i class="fa fa-comment"></i>{{count($article->comments)}}</span>
            <span class="news__author"><i class="fa fa-user"></i>{{$article->username}}</span>
        </div>
    </div>
    <div class="news__content">
        @if ($article->cover != "")
        <img class="news__cover" src="{{$article->cover}}"/>
        @endif
        <div class="news__short-content">
            {!! $article->short_content !!}
            <a class="news__read-more" href="{{$article->url}}">читать далее</a>
        </div>
    </div>

</div>