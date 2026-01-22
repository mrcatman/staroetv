@extends('layouts.default', ['vue' => true])
@section('content')
    <form class="form box" method="POST">
        <div class="box__breadcrumbs">
            <div class="breadcrumbs">
                @if ($channel)
                    <a class="breadcrumbs__item" href="{{typed_route('records.[RECORD].index', $channel->is_radio)}}">Архив</a>
                    <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
                @endif
                @if ($program)
                    <a class="breadcrumbs__item" href="{{$program->full_url}}">{{$program->name}}</a>
                    <a class="breadcrumbs__item breadcrumbs__item--current">Редактировать</a>
                @else
                    <a class="breadcrumbs__item breadcrumbs__item--current">Новая программа</a>
                @endif
            </div>
        </div>

        <div class="box__heading">
            <div class="box__heading__inner">
                {{ ($program ? "Редактировать программу: ".$program->name : "Добавить программу") }}
            </div>

        </div>
        <div class="box__inner">
            <div class="form__content">
                @csrf
                <input type="hidden" name="channel_id" value="{{$channel ? $channel->id : ''}}"/>
                <div class="tabs" data-id="programs-form">
                    <a class="tab tab--active" data-content="main">Основное</a>
                    <a class="tab" data-content="channels-history">Показ на других каналах</a>
                </div>
                <div class="response"></div>

                <div class="tab-content" data-id="programs-form" data-tab="main">
                    <div class="form__content">
                        <div class="input-container">
                            <label class="input-container__label">Название<span
                                    class="input-container__required">*</span></label>
                            <div class="input-container__inner">
                                <input class="input" name="name" id="channel_name"
                                       value="{{$program ? $program->name : ""}}"/>
                                <span class="input-container__message"></span>
                            </div>
                        </div>
                        @if (isset($all_channels) && $all_channels)
                            <div class="input-container">
                                <label class="input-container__label">Канал</label>
                                <div class="input-container__inner">
                                    <select class="select" name="channel_id">
                                        <option value="" selected>-</option>
                                        @foreach ($all_channels as $channel_id => $channel_name)
                                            <option value="{{$channel_id}}"
                                                    @if ($program->channel_id == $channel_id) selected="selected" @endif>{{$channel_name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-container__message"></span>
                                </div>
                            </div>
                        @endif
                        <div class="input-container">
                            <label class="input-container__label">Короткий URL</label>
                            <div class="input-container__inner">
                                <input class="input" name="url" id="channel_url"
                                       value="{{$program ? $program->url : ""}}"/>
                                <span class="input-container__message"></span>
                            </div>
                        </div>
                        <div class="input-container">
                            <label class="input-container__label">Описание</label>
                            <div class="input-container__inner">
                                <tiptap-editor name="description" :content='@json($program ? $program->description : '')'></tiptap-editor>
                                <span class="input-container__message"></span>
                            </div>
                        </div>
                        <div class="input-container">
                            <label class="input-container__label">Жанр</label>
                            <div class="input-container__inner">
                                <select class="select" name="genre_id">
                                    <option value="">Не выбран</option>
                                    @foreach(\App\Models\Genre::where(['type' => 'programs'])->get() as $genre)
                                        <option value="{{$genre->id}}"
                                                @if ($program && $program->genre_id == $genre->id) selected="selected" @endif>{{$genre->name}}</option>
                                    @endforeach
                                </select >
                                <span class="input-container__message"></span>
                            </div>
                        </div>
                        <div class="input-container">
                            <label class="input-container__label">Логотип</label>
                            <div class="input-container__inner">
                                <picture-uploader type="logo" name="cover_id"
                                                  :data="{{$program && $program->coverPicture ? $program->coverPicture : "null"}}"/>
                                <span class="input-container__message"></span>
                            </div>
                        </div>
                        <label class="input-container input-container--checkbox">
                            <input type="checkbox"
                                   name="show_full_titles" {{$program && $program->show_full_titles ? "checked" : ""}}>
                            <div class="input-container--checkbox__element"></div>
                            <div class="input-container__label">Показывать полные названия видео</div>
                        </label>

                        <div class="row">
                            <div class="col">
                                <div class="input-container input-container--vertical">
                                    <label class="input-container__label">Дата первого эфира</label>
                                    <div class="input-container__inner">
                                        <Datepicker name="date_of_start"
                                                    value="{{$program ? $program->date_of_start : ''}}"></Datepicker>
                                        <span class="input-container__message"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-container input-container--vertical">
                                    <label class="input-container__label">Дата последнего эфира</label>
                                    <div class="input-container__inner">
                                        <Datepicker can_be_now="true" name="date_of_closedown"
                                                    value="{{$program ? $program->date_of_closedown : ''}}"></Datepicker>
                                        <span class="input-container__message"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: none" class="tab-content" data-id="programs-form" data-tab="channels-history">
                    <additional-channels-editor :data='@json($program ? $program->additionalChannels : [])' :is_radio="@json($channel ? $channel->is_radio : false)"></additional-channels-editor>
                </div>
                <button class="button">Сохранить</button>
            </div>
        </div>

    </form>
    @if ($program)
        <form data-confirm="1"
              data-confirm-text="Вы уверены? Страница программы будет удалена, а все записи перейдут в выбранную вами"
              class="form box" action="{{route('programs.merge')}}" method="POST">
            <div class="box__heading">
                <div class="box__heading__inner">
                    Объединить программы
                </div>

            </div>
            <div class="box__inner">
                <input value="{{$program->id}}" type="hidden" name="original_id"/>
                <div class="response"></div>
                <div class="input-container">
                    <label class="input-container__label">Выберите программу</label>
                    <div class="input-container__element-outer">
                        <select class="select" name="merged_id">
                            @foreach ($all_programs as $program)
                                <option
                                    value="{{$program->id}}">{{$program->name}} @if (request()->input('all_programs'))
                                        ({{$program->channel ? $program->channel->name : ''}})
                                    @endif</option>
                            @endforeach
                        </select>
                        <div>
                            @if (!request()->input('all_programs'))
                                <a href="{{ request()->fullUrlWithQuery(['all_programs' => 1]) }}">Показать программы
                                    всех каналов</a>
                            @else
                                <a href="{{ request()->fullUrlWithQuery(['all_programs' => 0]) }}">Скрыть программы
                                    остальных каналов</a>
                            @endif
                        </div>

                    </div>
                    <span class="input-container__message"></span>
                </div>
                <button class="button">Объединить</button>
            </div>
            @csrf
        </form>
    @endif
@endsection
