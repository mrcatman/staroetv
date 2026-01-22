@extends('layouts.admin', ['vue' => true])
@section('admin-title')
    Лог действий пользователей
@endsection
@section('admin-content')
    <div class="actions-logs">
        <form class="row">
            @csrf
            <select class="select-classic" name="material_type">
                <option value="" @if (request()->input('material_type') == '') selected @endif>Все материалы</option>
                @foreach($material_types as $material_type => $name)
                    <option value="{{$material_type}}" @if (request()->input('material_type') == $material_type) selected @endif>{{$name}}</option>
                @endforeach
            </select>
            <input class="input" value="{{request()->input('material_id')}}" placeholder="ID" name="material_id">
            <button class="button" type="submit">Поиск</button>
        </form>
        <div class="horisontal-delimiter"></div>
        @foreach($logs as $log)
            @php($material = $log->material)
            <div class="actions-logs__item">
                <div class="actions-logs__item__top">
                    <span class="actions-logs__item__time">{{$log->created_at->format('d.m.Y H:i:s')}}</span>
                    &nbsp;
                    @if ($log->user)
                    <a href="{{route('users.show', $log->user)}}" class="actions-logs__item__link">{{$log->user->username}}</a>
                    @else
                        <span class="actions-logs__item__user">
                            Неизвестный юзер с ID {{$log->user_id}}
                        </span>
                    @endif
                    {{$log->action_name}}
                    {{$log->material_type_name}}
                    @if ($material)
                    <a href="{{$material->full_url ?? $material->url}}" class="actions-logs__item__link">
                        {{$log->material_name}}
                    </a>
                    @else
                        (удалено)
                    @endif
                </div>
                @if ($log->changes)
                    <a class="actions-logs__item__show-changes">показать изменения ({{count($log->changes)}})</a>
                <div class="actions-logs__item__changes" style="display: none">
                    @foreach($log->changes as $key => $change)
                        <div class="actions-logs__item__change">
                            <strong>{{$key}}</strong>:&nbsp;
                            {{count($change) > 1 ? implode(' -> ', $change) : $change[0]}}
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

        @endforeach

    </div>

    {{$logs->links()}}
@endsection
