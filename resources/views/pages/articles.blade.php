@extends('layouts.default')
@section('content')
    <div class="inner-page__content">
        <div class="row">
            <div class="col col-2">
                @foreach ($articles as $news_item)
                    @include('blocks/article', ['article' => $news_item])
                @endforeach
                {{$articles->links()}}
            </div>
        </div>
    </div>

@endsection
