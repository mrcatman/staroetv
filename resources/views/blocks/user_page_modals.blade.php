<div id="reputation_history" data-title="Репутация пользователя {{$user->username}} ({{$user->reputation_number}})" style="display:none">
    @include ('blocks/reputation_modal_content', ['reputation' => $user->reputation])
</div>

<div id="warnings_history" data-title="Баны пользователя {{$user->username}} ({{$user->ban_level}}%)" style="display:none">
    @include ('blocks/warnings_modal_content', ['warnings' => $user->warnings])
</div>

<div id="awards_history" data-title="Награды пользователя {{$user->username}} ({{count($user->awards)}})" style="display:none">
    @include ('blocks/awards_modal_content', ['awards' => $user->awards])
</div>

@include('blocks/change_reputation_modal')
