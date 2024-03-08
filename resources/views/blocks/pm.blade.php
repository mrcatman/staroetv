@php($pm = auth()->user()->unreadMessages())
<a  href="/pm" class="auth-panel__button auth-panel__button--pm">
    <span class="tooltip">Личные сообщения</span>
    <i class="fa fa-envelope"></i>
    <span style="display: none" class="auth-panel__button__count @if($pm > 0) auth-panel__button__count--visible @endif">{{$pm}}</span>
</a>
