<form class="form bb-editor @if (isset($inline) && $inline) bb-editor--inline @endif"
      data-callback="forumMessageCallback" action="{{isset($edit_id) && $edit_id ? route('forum.messages.update', $edit_id) : route('forum.messages.create')}}" method="POST" data-reset="1" data-auto-close-modal="1">

    @if (isset($edit_id))
        <input type="hidden" name="message_id" value="{{$edit_id}}"/>
    @endif
    @if (isset($topic_id))
        <input type="hidden" name="topic_id" value="{{$topic_id}}"/>
    @endif

        <input type="hidden" name="id" value=""/>

    @include('blocks.bb-editor.main', ['forum' => true])
</form>
