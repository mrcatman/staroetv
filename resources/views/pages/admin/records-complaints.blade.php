@extends('layouts.admin', ['vue' => true])
@section('admin-title')
    Жалобы на записи
@endsection
@section('admin-content')
    <div class="actions-logs">

        @foreach($complaints as $complaint)
            <div class="actions-logs__item">
                <div class="actions-logs__item__top">
                    <span class="actions-logs__item__time">{{$complaint->created_at->format('d.m.Y H:i:s')}}</span>
                    &nbsp;
                    @if ($complaint->user)
                        <a href="{{route('users.show', $complaint->user)}}" class="actions-logs__item__link">{{$complaint->user->username}}</a>
                    @else
                        <span class="actions-logs__item__user">
                            {{$complaint->contact}}
                        </span>
                    @endif
                    Жалоба на:
                    @if ($complaint->record)
                        <a href="{{$complaint->record->full_url}}" class="actions-logs__item__link">
                            {{$complaint->record->title}}
                        </a>
                    @else
                        (удалено)
                    @endif
                </div>
                <strong>{{$complaint->type_text}}</strong> {{$complaint->description}}
            </div>

        @endforeach

    </div>

    {{$complaints->links()}}
@endsection
