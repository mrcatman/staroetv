@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="box">
            <div class="box__heading">
                Панель редактора
            </div>
            <div class="box__inner">
                <div class="editor-panel">
                    @foreach ($materials as $material_type => $data)
                        @if (count($data['items']) > 0)
                        <div class="editor-panel__block">
                        <h2 class="editor-panel__title">{{$data['name']}}</h2>
                            @foreach ($data['items'] as $item)
                            <div class="editor-panel__row">
                                <a target="_blank" href="{{$item['url']}}" class="editor-panel__row__title">{{$item['name']}}</a>
                                <a class="button" data-approve="{{$data['id']}}" data-approve-id="{{$item['id']}}">Одобрить</a>
                            </div>
                            @endforeach
                        </div>
                        @endif

                    @endforeach
                </div>

            </div>
        </div>
    </div>
@endsection
