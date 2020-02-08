@php($pm = auth()->user()->unreadMessages())
<div class="auth-panel__pm__container"><a href="/pm" class="auth-panel__pm @if($pm > 0) auth-panel__pm--unread @endif">Личные сообщения @if ($pm > 0)(новых: {{$pm}})@endif</a></div>
