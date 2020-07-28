@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$page_title}} пользователя <a href="{{$user->url}}">{{$user->username}}</a></div>
        </div>
        <div class="inner-page__content">
            <div class="box">
                <div class="box__inner">
                    <div class="records-list">
                       @foreach($records as $record)
                            @include('blocks/record', ['record' => $record])
                        @endforeach
                    </div>
                </div>
                <div class="comments__pager">
                    {{$records->links()}}
                </div>
            </div>
        </div>
    </div>

@endsection
