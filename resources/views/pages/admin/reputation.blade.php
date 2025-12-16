@extends('layouts.admin')
@section('admin-title')
    Последние изменения в репутации
@endsection
@section('admin-content')

       <table class="admin-panel__table admin-panel__table--bigger">
           <thead>
                <tr>
                    <td>Пользователь</td>
                    <td>От кого</td>
                    <td>Число</td>
                    <td>Дата</td>
                    <td>Комментарий</td>
                    <td>Источник</td>
                </tr>
           </thead>
           <tbody>
                @foreach ($reputation as $reputation_item)
                <tr>
                    <td style="white-space: nowrap">
                        @if($reputation_item->to)
                        <a href="/index/8-{{$reputation_item->to->id}}">{{$reputation_item->to->username}}</a>
                        @endif
                    </td>
                    <td style="white-space: nowrap">
                        @if($reputation_item->from)
                            <a href="{{route('users.show', $reputation_item->from->id)}}">{{$reputation_item->from->username}}</a>
                        @endif
                    </td>
                    <td style="white-space: nowrap">
                        {{$reputation_item->weight}}
                    </td>
                    <td style="white-space: nowrap">
                        {{$reputation_item->created_at->format('d.m.Y H:i')}}
                    </td>
                    <td>
                        {{$reputation_item->comment}}
                    </td>
                    <td>
                        @if($reputation_item->link)
                            <a target="_blank" href="{{$reputation_item->link}}">Перейти</a>
                        @endif
                    </td>
                </tr>
                @endforeach
           </tbody>
       </table>
        <div class="pager-container pager-container--light">
            {{$reputation->links()}}
        </div>

@endsection
