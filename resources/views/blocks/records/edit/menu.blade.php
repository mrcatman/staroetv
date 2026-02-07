@foreach ($actions as $action)
    <a class="menu__item" data-url="{{$action['url']}}" @if (isset($action['instant']) && $action['instant']) data-instant="1" @endif>{{$action['name']}}</a>
@endforeach
