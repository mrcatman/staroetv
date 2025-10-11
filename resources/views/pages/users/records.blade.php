@extends('layouts.default')
@section('content')
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                <span>
                         {{$page_title}} пользователя <a href="{{$user->url}}">{{$user->username}}</a>
                </span>

            </div>
        </div>
        <div class="box__inner">
            <div class="records-list">
                @foreach($records as $record)
                    @if ($record->is_radio)
                        @include('blocks/radio_recording', ['record' => $record])
                    @else
                        @include('blocks/record', ['record' => $record])
                    @endif
                @endforeach
            </div>
        </div>
        <div class="box__pager">
            {{$records->links()}}
        </div>
    </div>

@endsection
