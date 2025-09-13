<div class="record-page__player-container teletext-page__player-container">
    <div class="teletext">
        {!! $content !!}
    </div>
</div>
<div class="box box--top teletext-controls">
    <input type="hidden" name="cover_id" value="{{$teletext->cover_id}}" />
    <input type="hidden" name="channel_id" value="{{$teletext->channel_id}}" />
    <input type="hidden" name="teletext_id" value="{{$teletext->id}}" />
    <div class="box__inner">
        <div class="teletext-controls__inner">
            <a href="?page={{$navigation['prev']}}" class="button button--light teletext-controls__button teletext-controls__prev">
                <<
            </a>
            <select class="input teletext-controls__select">
                @foreach($teletext->pages as $teletext_page)
                    <option @if ($page == $teletext_page) selected
                            @endif value="{{$teletext_page}}">{{$teletext_page}}</option>
                @endforeach
            </select>
            <a href="?page={{$navigation['next']}}" class="button button--light teletext-controls__button teletext-controls__next">
                >>
            </a>
        </div>

    </div>
</div>
