<div class="awards-history">
@php($can_edit_awards = \App\Helpers\PermissionsHelper::allows('awado'))
@foreach ($awards as $award)
    <div class="awards-history__item modal-window__list-item" data-id="{{$award->id}}">
        <img class="awards-history__item__picture" src="{{$award->award->picture->url}}"/>
            <div class="awards-history__item__texts">
            <div class="awards-history__item__top">
                <a @if ($award->from) target="_blank" href="{{$award->from->url}}" @endif class="awards-history__item__user">{{$award->from ? $award->from->username : "DELETED"}}</a>
                <span class="awards-history__item__date">{{$award->created_at}}</span>
                @if ($can_edit_awards)
                <div class="modal-window__buttons awards-history__item__buttons">
                    <a class="modal-window__button awards-history__item__button awards-history__item__button--edit">
                        <span class="tooltip">Редактировать</span>
                        <i class="fa fa-edit"></i>
                    </a>
                    <a class="modal-window__button awards-history__item__button awards-history__item__button--delete">
                        <span class="tooltip">Удалить</span>
                        <i class="fa fa-times"></i>
                    </a>
                </div>
                @endif
            </div>
            <div class="awards-history__item__comment">
               {{$award->comment}}
            </div>
            @if ($can_edit_awards)
              <form data-callback="editAwardCallback" data-noscroll="1" method="POST" class="form awards-history__item__form" action="/awards/edit" style="display: none">
                  <input type="hidden" name="id" value="{{$award->id}}"/>
                  <div class="input-container input-container--vertical">
                      <div class="input-container__inner">
                          <textarea class="input input--textarea" name="comment">{{$award->comment}}</textarea>
                      </div>
                  </div>
                  <div class="form__bottom">
                      <button class="button button--light">ОК</button>
                      <a class="button button--light button--cancel">Отмена</a>
                      <div class="response response--light"></div>
                  </div>
              </form>
            @endif
        </div>
    </div>
@endforeach
</div>