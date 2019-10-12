@extends('layouts.default')
@section('content')
    <div class="inner-page__content">
        <div class="row">
            <div class="col">
                @foreach ($articles as $news_item)
                    @include('blocks/article', ['article' => $news_item])
                @endforeach
                <div class="pager-container pager-container--light">
                    {{$articles->links()}}
                </div>
              
            </div>
        </div>
    </div>

@endsection
