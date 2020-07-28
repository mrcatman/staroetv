<div class="questionnaire" data-id="{{$questionnaire->id}}">
    <div class="questionnaire__title">{{$questionnaire->title}}</div>
    @if (!isset($show_results) || !$show_results)
        <form data-noscroll="1" action="/questionnaire/vote" class="form questionnaire__variants">
            @csrf
            <input type="hidden" name="questionnaire_id" value="{{$questionnaire->id}}">
            @foreach ($questionnaire->variants as $variant)
                <div class="questionnaire__variant">
                    <label class="input-container @if($questionnaire->multiple_variants) input-container--checkbox @else input-container--radio @endif">
                        @if($questionnaire->multiple_variants)
                            <input type="checkbox" name="variant" value="{{$variant->id}}">
                            <div class="input-container--checkbox__element"></div>
                        @else
                            <input type="radio" name="variant" value="{{$variant->id}}">
                            <div class="input-container--radio__element"></div>
                        @endif
                        <div class="input-container__label">{{$variant->title}}</div>
                    </label>
                </div>
            @endforeach
            @if (auth()->user())
            <div class="questionnaire__vote">
                <button class="button questionnaire__vote__button">Проголосовать</button>
                <div class="response response--light"></div>
            </div>
            @endif
        </form>
    </div>
    @else
        <div class="questionnaire__results">
            @foreach ($questionnaire->variants as $variant)
                <div class="questionnaire__result">
                    <div class="questionnaire__result__title">{{$variant->title}}</div>
                    <div class="questionnaire__result__count">[{{$variant->answers_count}}]</div>
                    <div class="questionnaire__result__bar" style="width: {{$variant->answers_count === 0 ? 0 : $variant->answers_count / $questionnaire->total_answers * 100}}%"></div>
                    <div class="questionnaire__result__percent">{{ $variant->answers_count === 0 ? 0 : number_format($variant->answers_count / $questionnaire->total_answers * 100, 2, '.', '')}}%</div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="questionnaire__bottom">
        <div class="questionnaire__total">
            Всего ответов: <strong>{{$questionnaire->total_answers}}</strong>
        </div>
        @if (auth()->user())
        <form class="form" action="/questionnaire/form">
            @csrf
            <input type="hidden" name="questionnaire_id" value="{{$questionnaire->id}}"/>
            @if (!isset($show_results) || !$show_results)
                <input type="hidden" name="show_results" value="1"/>
                <button class="button">Посмотреть результаты</button>
                @else
                <input type="hidden" name="show_results" value="0"/>
                <button class="button">Переголосовать</button>
            @endif
        </form>
        @endif
    </div>
</div>