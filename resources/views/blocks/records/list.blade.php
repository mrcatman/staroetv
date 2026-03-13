@if (!isset($records_data) || !$records_data)
    @php($records_data = \App\Helpers\RecordsHelper::get($conditions))
@endif
@php($new_titles = isset($conditions['new_titles']) ? $conditions['new_titles'] : false)
@php($show_advanced_filters = count($records_data['records']) >= 5)
@php($hide_if_zero = isset($hide_if_zero) ? $hide_if_zero : false)
@php($block_title = isset($block_title) ? $block_title : "Записи")
@php($is_radio = isset($conditions['is_radio']) && $conditions['is_radio'])

@if (!$hide_if_zero || count($records_data['records']) > 0)
    @if (!isset($ajax) || !$ajax)
        <div class="box box--dark records-list__ajax-container records-list__outer @if(isset($class)) {{$class}} @endif"
             data-block-title="{{$block_title}}" data-conditions="{{json_encode($conditions)}}"
             @if (isset($title_param)) data-title-param="{{$title_param}}" @endif>
            @endif
            <div class="box__heading">
                <div class="box__heading__inner">
                    {{$block_title}}&nbsp;<span class="box__heading__count">{{$records_data['count']}}</span>
                </div>
            </div>
            <div class="box__inner">
                <div class="records-list__filters">
                    <div class="records-list__sort">
                        @if ($show_advanced_filters)
                        <div class="top-list records-list__sort__items">
                            <a data-sort="newer" class="top-list__item @if ($records_data['sort'] == "newer") top-list__item--active @endif"
                               href="{{$records_data['base_url']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'sort']), ['sort' => 'newer']))}}">От
                                новых к старым</a>
                            <a data-sort="older" class="top-list__item @if ($records_data['sort'] == "older") top-list__item--active @endif"
                               href="{{$records_data['base_url']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'sort']), ['sort' => 'older']))}}">От
                                старых к новым</a>
                            <a data-sort="added" class="top-list__item @if ($records_data['sort'] == "added") top-list__item--active @endif"
                               href="{{$records_data['base_url']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'sort']), ['sort' => 'added']))}}">Недавно
                                добавленные</a>
                        </div>
                        <select class="select-classic records-list__sort__mobile">
                            <option value="newer" @if ($records_data['sort'] == 'newer') selected @endif>От новых к
                                старым
                            </option>
                            <option value="older" @if ($records_data['sort'] == 'older') selected @endif>От старых к
                                новым
                            </option>
                            <option value="added" @if ($records_data['sort'] == 'added') selected @endif>Недавно
                                добавленные
                            </option>
                        </select>
                        @endif
                        <div class="input-container records-list__sort__search">
                            <div class="input-container__inner input-container__inner--with-icon">
                                <i class="fa fa-search input-container__icon"></i>
                                <input value="{{$records_data['search']}}" class="input"
                                       placeholder="Поиск по разделу..."/>
                            </div>
                        </div>
                    </div>
                    @if ($show_advanced_filters)
                    @if ($records_data['years'])
                        <div class="top-list records-list__years">
                            <a class="top-list__item top-list__item--all @if (!$records_data['selected_year']) top-list__item--active @endif"
                               href="{{$records_data['base_url']}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'year', 'month', 'page']))}}">
                                <span class="top-list__item__name">Все годы</span>
                            </a>
                            @foreach ($records_data['years'] as $year => $count)
                                <a data-year="{{$year}}" class="top-list__item @if ($records_data['selected_year'] == $year) top-list__item--active @endif"
                                   href="{{$records_data['base_url']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'year', 'month', 'page']), ['year' => $year]))}}">
                                    <span class="top-list__item__name">{{$year}}</span>
                                    <span class="top-list__item__count">{{$count}}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                    @if ($records_data['months'])
                        @php($month_names = \App\Helpers\DatesHelper::monthNames())
                        <div class="top-list records-list__months">
                            <a class="top-list__item top-list__item--all @if (!$records_data['selected_month']) top-list__item--active @endif"
                               href="{{$records_data['base_url']}}?{{http_build_query(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'month']))}}">
                                <span class="top-list__item__name">Все месяцы</span>
                            </a>
                            @foreach ($records_data['months'] as $month => $count)
                                @if (isset($month_names[$month - 1]))
                                    <a data-month="{{$month}}" class="top-list__item @if ($records_data['selected_month'] == $month) top-list__item--active @endif"
                                       href="{{$records_data['base_url']}}?{{http_build_query(array_merge(\App\Helpers\ArraysHelper::diffAssoc($records_data['query_params'], ['conditions', 'month']), ['month' => $month]))}}">
                                        <span class="top-list__item__name">{{$month_names[$month - 1]}}</span>
                                        <span class="top-list__item__count">{{$count}}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @endif
                </div>

                @php($nothing_found = isset($search) && $search != '' && count($records_data['records']) === 0)
                <div class="records-list @if(!$is_radio && !$nothing_found) records-list--thumbs @endif">
                    @if ($nothing_found)
                        <div class="records-list__nothing-found">По запросу <strong>"{{$search}}"</strong> ничего не
                            найдено
                        </div>
                    @endif
                    @foreach($records_data['records'] as $record)
                        @php($data = ['record' => $record])
                        @if (isset($title_param))
                            @php($data['title'] = $record->{$title_param})
                        @endif
                        @if ($is_radio)
                            @include('blocks.records.radio-item', $data)
                        @else
                            @include('blocks.records.item', $data)
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="box__pager">
                {{$records_data['records']->appends(request()->except('_token'))->links()}}
            </div>
            @if (!isset($ajax) || !$ajax)
        </div>
    @endif
@endif
