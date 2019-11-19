<div class="reputation-history">
@php($can_edit_reputation = \App\Helpers\PermissionsHelper::allows('editrep'))
@foreach ($reputation as $reputation_item)
     @php($can_reply = $can_edit_reputation || (auth()->user() && $reputation_item->to_id == auth()->user()->id))
    <div class="modal-window__list-item reputation-history__item" data-id="{{$reputation_item->id}}">
        <div class="reputation-history__item__top">
            <a @if ($reputation_item->from) target="_blank" href="{{$reputation_item->from->url}}" @endif class="reputation-history__item__user">{{$reputation_item->from ? $reputation_item->from->username : "DELETED"}}</a>
            <span class="reputation-history__item__weight @if ($reputation_item->weight > 0) reputation-history__item__weight--positive @elseif ($reputation_item->weight < 0) reputation-history__item__weight--negative @else reputation-history__item__weight--neutral @endif">
                {{$reputation_item->weight > 0 ? "+".$reputation_item->weight  : $reputation_item->weight }}
            </span>
            <span class="reputation-history__item__date">{{$reputation_item->created_at}}</span>
            @if ($reputation_item->link)<a href="{{$reputation_item->link}}" target="_blank" class="reputation-history__item__source">[Источник]</a>@endif
            <div class="modal-window__buttons reputation-history__item__buttons">
                @if ($can_reply)
                <a class="modal-window__button reputation-history__item__button reputation-history__item__button--reply">
                    <span class="tooltip">Ответить</span>
                    <i class="fa fa-check"></i>
                </a>
                @endif
                @if ($can_edit_reputation)
                    <a class="modal-window__button reputation-history__item__button reputation-history__item__button--edit">
                        <span class="tooltip">Редактировать</span>
                        <i class="fa fa-edit"></i>
                    </a>
                    <a class="modal-window__button reputation-history__item__button reputation-history__item__button--delete">
                        <span class="tooltip">Удалить</span>
                        <i class="fa fa-times"></i>
                    </a>
                @endif
            </div>
        </div>
        <div class="reputation-history__item__comment">
            {{$reputation_item->comment}}
        </div>
        @if ($can_edit_reputation)
        <form data-callback="editReputationCallback" data-noscroll="1" method="POST" class="form reputation-history__item__form" action="/reputation/edit" style="display: none">
            <input type="hidden" name="id" value="{{$reputation_item->id}}"/>
            <div class="input-container input-container--vertical">
                <div class="input-container__inner">
                    <textarea class="input input--textarea" name="comment">{{$reputation_item->comment}}</textarea>
                </div>
            </div>
            <div class="form__bottom">
                <button class="button button--light">ОК</button>
                <a class="button button--light button--cancel">Отмена</a>
                <div class="response response--light"></div>
            </div>
        </form>
        @endif
        @if ($can_reply)
            <form data-callback="replyReputationCallback" data-noscroll="1" method="POST" class="form reputation-history__item__reply-form" action="/reputation/reply" style="display: none">
                <input type="hidden" name="id" value="{{$reputation_item->id}}"/>
                <div class="input-container input-container--vertical">
                    <div class="input-container__inner">
                        <textarea class="input input--textarea" name="reply_comment">{{$reputation_item->reply_comment}}</textarea>
                    </div>
                </div>
                <div class="form__bottom">
                    <button class="button button--light">ОК</button>
                    <a class="button button--light button--cancel">Отмена</a>
                    <div class="response response--light"></div>
                </div>
            </form>
        @endif
        <div class="reputation-history__item__reply-comment" @if ($reputation_item->reply_comment == "") style="display: none" @endif>
            <strong>Ответ: </strong><span class="reputation-history__item__reply-comment__text">{{$reputation_item->reply_comment}}</span>
        </div>
    </div>
@endforeach
</div>