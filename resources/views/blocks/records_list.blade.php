@if (!isset($records_data) || !$records_data)
@php($records_data = \App\Helpers\RecordsHelper::get($conditions))
@endif
@php($hide_if_zero = isset($hide_if_zero) ? $hide_if_zero : false)
@php($block_title = isset($block_title) ? $block_title : "Записи")
@if (!$hide_if_zero || count($records_data['records']) > 0)
@if (!isset($ajax) || !$ajax)
<div class="box box--dark records-list__outer @if(isset($class)) {{$class}} @endif" data-block-title="{{$block_title}}" data-conditions="{{json_encode($conditions)}}" @if (isset($title_param)) data-title-param="{{$title_param}}" @endif>
@endif
    <div class="box__heading">
        <div class="box__heading__inner">
            {{$block_title}} <span class="box__heading__count">{{$records_data['count']}}</span>
        </div>
    </div>
    <div class="box__inner">
        <div class="records-list__filters">

            <div class="records-list__sort">
                <div class="records-list__sort__items">
                    <a class="records-list__sort__item @if ($records_data['sort'] == "newer") records-list__sort__item--active @endif" href="{{$records_data['base_link']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'sort']), ['sort' => 'newer']))}}">От новых к старым</a>
                    <a class="records-list__sort__item @if ($records_data['sort'] == "older") records-list__sort__item--active @endif" href="{{$records_data['base_link']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'sort']), ['sort' => 'older']))}}">От старых к новым</a>
                    <a class="records-list__sort__item @if ($records_data['sort'] == "added") records-list__sort__item--active @endif" href="{{$records_data['base_link']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'sort']), ['sort' => 'added']))}}">Недавно добавленные</a>
                </div>
                <select class="select-classic records-list__sort__mobile">
                    <option value="newer" @if ($records_data['sort'] == 'newer') selected @endif>От новых к старым</option>
                    <option value="older" @if ($records_data['sort'] == 'older') selected @endif>От старых к новым</option>
                    <option value="added" @if ($records_data['sort'] == 'added') selected @endif>Недавно добавленные</option>
                </select>
                <div class="records-list__sort__search">
                    <input value="{{$records_data['search']}}" class="input" placeholder="Поиск по разделу..."/>
                </div>
            </div>
            @if ($records_data['years'])
                <div class="top-list records-list__years">
                    <a class="top-list__item top-list__item--all @if (!$records_data['selected_year']) top-list__item--active @endif"  href="{{$records_data['base_link']}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'year', 'month']))}}">
                        <span class="top-list__item__name">Все годы</span>
                    </a>
                    @foreach ($records_data['years'] as $year => $count)
                        <a class="top-list__item @if ($records_data['selected_year'] == $year) top-list__item--active @endif" href="{{$records_data['base_link']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'year', 'month']), ['year' => $year]))}}">
                            <span class="top-list__item__name">{{$year}}</span>
                            <span class="top-list__item__count">{{$count}}</span>
                        </a>
                    @endforeach
                </div>
            @endif
            @if ($records_data['months'])
                @php($month_names = \App\Helpers\DatesHelper::monthNames())
                <div class="top-list records-list__months">
                    <a class="top-list__item top-list__item--all @if (!$records_data['selected_month']) top-list__item--active @endif"  href="{{$records_data['base_link']}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'month']))}}">
                        <span class="top-list__item__name">Все месяцы</span>
                    </a>
                    @foreach ($records_data['months'] as $month => $count)
                        <a class="top-list__item @if ($records_data['selected_month'] == $month) top-list__item--active @endif" href="{{$records_data['base_link']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'month']), ['month' => $month]))}}">
                            <span class="top-list__item__name">{{$month_names[$month - 1]}}</span>
                            <span class="top-list__item__count">{{$count}}</span>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>

        @php($is_radio = isset($conditions['is_radio']) && $conditions['is_radio'])
        <div class="records-list @if(!$is_radio) records-list--thumbs @endif">
            @if (isset($search) && $search != '' && count($records_data['records']) === 0)
                <div class="records-list__nothing-found">По запросу <strong>"{{$search}}"</strong> ничего не найдено</div>
            @endif
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
@endif
