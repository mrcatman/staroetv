@extends('layouts.redactor')
@section('redactor-title')
    Модерация контента
@endsection
@section('redactor-content')
    <div class="editor-panel">
        @foreach ($materials as $material_type => $data)
            @if (count($data['items']) > 0)
                <div class="editor-panel__block">
                    <h2 class="editor-panel__title">{{$data['name']}}</h2>
                    <div class="editor-panel__items">
                    @foreach ($data['items'] as $item)
                        <div class="editor-panel__item">
                            <div class="editor-panel__item__texts">
                                <a target="_blank" href="{{$item['url']}}"
                                   class="editor-panel__item__title">{{$item['name']}}</a>
                                <div class="icon-blocks">
                                    @if ($item['user'])
                                        <a href="{{$item['user']->url}}" class="icon-block">
                                            <i class="fa fa-user"></i>
                                            <span class="icon-block__text">{{$item['user']->username}}</span>
                                        </a>
                                    @endif
                                    <span class="icon-block">
                                        <i class="fa fa-clock"></i>
                                        <span class="icon-block__text">{{$item['created_at']}}</span>
                                    </span>
                                </div>
                            </div>

                            <div class="buttons-row buttons-row--nowrap">
                            <a class="button" data-approve="{{$data['id']}}"
                               data-approve-id="{{$item['id']}}">Одобрить</a>
                            <a class="button button--light"
                               data-confirm-form-disable-redirects="1"
                               data-confirm-form-input-value="{{$item['id']}}"
                               data-confirm-form-text="Вы уверены, что хотите удалить эту запись?"
                               data-confirm-form-url="{{route($data['id'].'.delete')}}"
                            >Удалить</a>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            @endif

        @endforeach
    </div>

@endsection
