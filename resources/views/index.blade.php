@extends('layouts.default')
@section('content')
    @php($news = \App\Article::where(['type_id' => \App\Article::TYPE_NEWS, 'pending' => false])->orderBy('created_at', 'desc')->paginate(10))
    <div class="inner-page__content">
        <div class="row">
            <div class="col ">
                @foreach ($news as $news_item)
                    @include('blocks/article', ['article' => $news_item])
                @endforeach
                <div class="pager-container pager-container--light">
                    {{$news->links()}}
                </div>
            </div>
        </div>
    </div>
@endsection
