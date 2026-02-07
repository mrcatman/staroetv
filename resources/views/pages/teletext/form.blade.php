@extends('layouts.default', ['vue' => true])
@section('content')
    <form enctype="multipart/form-data" class="form box box--top" method="POST">
        <div class="box__heading">
            <div class="box__heading__inner">
                {{ $teletext ? "Редактировать телетекст" : "Добавить телетекст" }}
            </div>

            <div class="box__heading__right">
                <a href="{{route('teletext.index')}}" class="button button--light">Назад</a>
            </div>
        </div>
        <div class="box__inner">
            <div class="form__content">
                <div class="response"></div>
                <div class="input-container">
                    <label for="file" class="input-container__label">Файл</label>
                    <div class="input-container__inner">
                        <div class="input-container__element-outer">
                            <input type="file" name="file"/>
                            <div class="input-container__description">Файл формата .t42</div>
                        </div>
                        <span class="input-container__message"></span>
                    </div>

                </div>

                <div class="input-container">
                    <label for="channel_id" class="input-container__label">Канал</label>
                    <div class="input-container__inner">
                        <channel-select :channel='@json($teletext ? $teletext->channel : null)' :channels-list='@json($channels)'></channel-select>
                        <span class="input-container__message"></span>
                    </div>
                </div>

                <div class="input-container">
                    <label for="year" class="input-container__label">Дата</label>
                    <div class="input-container__inner">
                        <div class="input-container__element-outer">
                            <date-select name="date" :date='@json($teletext ? ['year' => $teletext->year, 'month' => $teletext->month, 'day' => $teletext->day] : null)'></date-select>
                        </div>

                        <span class="input-container__message"></span>
                    </div>
                </div>


                <div class="input-container">
                    <label for="quality" class="input-container__label">Качество оцифровки</label>
                    <div class="input-container__inner input-container__inner--autowidth">
                        <div class="input-container__element-outer">
                            <div class="radio-buttons radio-buttons--tabs">
                                @for ($i = 1; $i <= 10;$i++)
                                    <label class="radio-button radio-button--tabs">
                                        <input type="radio" name="quality"
                                               @if (($teletext && $teletext->quality == $i) || (!$teletext && $i === 7)) checked="checked" @endif value={{$i}} />
                                        <div class="radio-button--tabs__variant">{{$i}}</div>
                                    </label>
                                @endfor
                            </div>
                        </div>

                        <span class="input-container__message"></span>
                    </div>
                </div>

                <div class="input-container">
                    <label for="description" class="input-container__label">Описание</label>
                    <div class="input-container__inner">
                        <input class="input" name="description" value="{{$teletext ? $teletext->description : ""}}"/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <button class="button">Сохранить</button>
            </div>

        </div>
        @csrf
    </form>
@endsection
