@if (!isset($records_data) || !$records_data)
@php($records_data = \App\Helpers\RecordsHelper::get($conditions))
@endif
@php($block_title = isset($block_title) ? $block_title : "Записи")
@if (!isset($ajax) || !$ajax)

<div class="box box--dark records-list__outer @if(isset($class)) {{$class}} @endif" data-block-title="{{$block_title}}" data-conditions="{{json_encode($conditions)}}" @if (isset($title_param)) data-title-param="{{$title_param}}" @endif>
@endif
    <div class="box__heading">
        <div class="box__heading__inner">
            {{$block_title}} <span class="box__heading__count">{{$records_data['count']}}</span>
        </div>
    </div>
    <div class="box__inner">
        <div class="records-list__sort">
            <div class="records-list__sort__items">
                <a class="records-list__sort__item @if ($records_data['sort'] == "newer") records-list__sort__item--active @endif" data-sort="newer">От новых к старым</a>
                <a class="records-list__sort__item @if ($records_data['sort'] == "older") records-list__sort__item--active @endif" data-sort="older">От старых к новым</a>
                <a class="records-list__sort__item @if ($records_data['sort'] == "added") records-list__sort__item--active @endif" data-sort="added">Недавно добавленные</a>
            </div>
            <select class="select-classic records-list__sort__mobile">
                <option value="newer">От новых к старым</option>
                <option value="older">От старых к новым</option>
                <option value="added">Недавно добавленные</option>
            </select>
            <div class="records-list__sort__search">
                <input value="{{$records_data['search']}}" class="input" placeholder="Поиск по разделу..."/>
            </div>
        </div>
        @php($is_radio = isset($conditions['is_radio']) && $conditions['is_radio'])
        <div class="records-list @if(!$is_radio) records-list--thumbs @endif">
            @foreach($records_data['records'] as $record)
                @php($data = ['record' => $record])
                @if (isset($title_param))
                    @php($data['title'] = $record->{$title_param})
                @endif
                @if ($is_radio)
                @include('blocks/radio_recording', $data)
                @else
                @include('blocks/record', $data)
                @endif
            @endforeach
        </div>
        <div class="records-list__pager-container">
            {{$records_data['records']->links()}}
        </div>
    </div>
@if (!isset($ajax) || !$ajax)
</div>
@endif
