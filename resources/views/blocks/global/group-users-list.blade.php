<div class="group-users-list">
    @foreach ($users as $index => $user)
        <a target="_blank" href="{{$user->url}}" class="user-online" data-group-id="{{$user->group_id}}">{{$user->username}}</a>@if ($index != count($users) - 1) , @endif
    @endforeach
</div>
