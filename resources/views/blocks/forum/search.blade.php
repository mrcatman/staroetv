@php($forum = isset($forum) ? $forum : null)
<form @if (!$forum) action="{{route('forum.index')}}"
      @else action="{{route('forum.subforums.show', $forum)}}" @endif method="GET"
      class="forum-section__search forum-section__search--subforum">
    <input @if ($forum) placeholder="Поиск по подфоруму" @else placeholder="Поиск по форуму"
           @endif class="input forum-section__search--subforum__input" name="s" value="{{$search}}">
    <select class="select-classic forum-section__search--subforum__type" name="type">
        <option value="topics"
                @if (!isset($messages_view) || !$messages_view) selected @endif>Темы
        </option>
        <option value="messages"
                @if (isset($messages_view) && $messages_view) selected @endif>Сообщения
        </option>
    </select>
    <button type="submit" class="button forum-section__search--subforum__button"><i class="fa fa-search"></i>Искать</button>
</form>
