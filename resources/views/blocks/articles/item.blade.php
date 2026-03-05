<div class="news @if ($article->pending) news--pending @endif @if ($article->cover_url != "") news--with-picture @else news--without-picture @endif @if (isset($full_width) && $full_width) news--full-width @endif @if (isset($fill) && $fill) news--fill @endif @if (isset($before_fill) && $before_fill) news--before-fill @endif @if (isset($first) && $first) news--first @endif @if (isset($last) && $last) news--last @endif @if (isset($before_last) && $before_last) news--before-last @endif">
    <div class="news__content">
        <div class="news__top">
            @if (isset($search))
                <a href="{{$article->full_url}}" class="news__title">{!! \App\Helpers\HighlightHelper::highlight($article->title, $search) !!}</a>
            @else
            <a href="{{$article->full_url}}" class="news__title">{{$article->title}}</a>
            @endif
            <div class="news__info">
                <span class="news__date"><i class="fa fa-calendar"></i>{{$article->created_at}}</span>
                <span class="news__views"><i class="fa fa-eye"></i>{{$article->views}}</span>
                <span class="news__comments"><i class="fa fa-comment"></i>{{$article->comments_count}}</span>
                <span class="news__author"><a href="{{route('users.show-by-username', $article->username)}}"><i class="fa fa-user"></i>{{$article->username}}</a></span>
            </div>
        </div>
        <a href="{{$article->full_url}}" class="news__short-content">
        @if (isset($search))
            {!! $article->searchContent($search) !!}
            @else
            {!! $article->short_content !!}
            @endif
           <!-- <a class="news__read-more" href="{{$article->full_url}}">читать далее</a> -->
        </a>
    </div>
    @if ($article->cover_url != "")
       <a href="{{$article->full_url}}" class="news__cover">
           <div class="news__cover__inner"  style="background-image:url({{$article->cover_url}})"></div>
       </a>
     @endif
</div>
