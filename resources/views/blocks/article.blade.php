<div class="news @if ($article->pending) news--pending @endif @if ($article->cover_url != "") news--with-picture @else news--without-picture @endif @if (isset($full_width) && $full_width) news--full-width @endif @if (isset($fill) && $fill) news--fill @endif @if (isset($before_fill) && $before_fill) news--before-fill @endif @if (isset($first) && $first) news--first @endif @if (isset($last) && $last) news--last @endif @if (isset($before_last) && $before_last) news--before-last @endif">
    <div class="news__content">
        @if (isset($show_actions_panel) && $show_actions_panel)
        <div class="news__actions">
             <span data-id="{{$article->id}}" class="button button--dropdown button--small button--light button--article-menu">
                 <span class="button--dropdown__text">Действия</span>
                 <span class="button--dropdown__icon">
                     <i class="fa fa-chevron-down"></i>
                 </span>
                 <div class="button--dropdown__list" id="actions_list_{{$article->id}}"></div>
             </span>
        </div>
        @endif
        <div class="news__top">
            @if (isset($search))
                <a href="{{$article->url}}" class="news__title">{!! \App\Helpers\HighlightHelper::highlight($article->title, $search, true) !!}</a>
            @else
            <a href="{{$article->url}}" class="news__title">{{$article->title}}</a>
            @endif
            <div class="news__info">
                <span class="news__date"><i class="fa fa-calendar"></i>{{$article->created_at}}</span>
                <span class="news__views"><i class="fa fa-eye"></i>{{$article->views}}</span>
                <span class="news__comments"><i class="fa fa-comment"></i>{{$article->comments_count}}</span>
                <span class="news__author"><a href="/index/8-0-{{$article->username}}"><i class="fa fa-user"></i>{{$article->username}}</a></span>
            </div>
        </div>
        <a href="{{$article->url}}" class="news__short-content">
        @if (isset($search))
            {!! $article->searchContent($search) !!}
            @else
            {!! $article->short_content !!}
            @endif
           <!-- <a class="news__read-more" href="{{$article->url}}">читать далее</a> -->
        </a>
    </div>
    @if ($article->cover_url != "")
       <a href="{{$article->url}}" class="news__cover">
           <div class="news__cover__inner"  style="background-image:url({{$article->cover_url}})"></div>
       </a>
     @endif
</div>
