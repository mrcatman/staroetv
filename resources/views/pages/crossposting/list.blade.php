@extends('layouts.default')
@section('page-title')
    Посты в соцсетях
@endsection
@section('content')
    <div class="box">
        <div class="box__heading">
            Посты в соцсетях
            <div class="box__heading__right">
                <a href="/crossposts/add" class="button">Создать новый пост</a>
            </div>
        </div>
        <div class="box__inner">
            @foreach ($crossposts as $crosspost)
                <div class="event  event--big  ">
                    @if ($crosspost->cover)
                    <a href="/crossposts/{{$crosspost->id}}/edit" class="event__cover" style="background-image: url({{$crosspost->cover}});"></a>
                    @endif

                    <div class="event__texts">
                        <a href="/crossposts/{{$crosspost->id}}/edit" class="event__title">
                            {{$crosspost->title}}
                        </a>
                        @if ($crosspost->post_ts)
                            <span class="event__date">{{$crosspost->post_time}}</span>
                        @endif
                    </div>
                    <div style="flex:1;display:flex;justify-content: flex-end">
                        <a class="button button--light" data-confirm-form-input-name="crosspost_id" data-confirm-form-input-value="{{$crosspost->id}}" data-confirm-form-text="Вы уверены, что хотите удалить пост?" data-confirm-form-url="/crossposts/delete">Удалить</a>
                    </div>
                </div>

            @endforeach
            <div class="comments__pager">
                {{$crossposts->appends(request()->except('_token'))->links()}}
            </div>
        </div>
   </div>
@endsection
