<a href="{{$teletext->url}}" class="record-item teletext-item @if ($teletext->pending) record-item--pending @endif">
    <div class="record-item__cover teletext-item__cover" style="background-image: url('{{$teletext->cover}}')"></div>
    <div class="record-item__texts">
        <span class="record-item__title">
            {{$teletext->title}}
        </span>
        <div class="record-item__info">
            <span class="record-item__views"><i class="fa fa-eye"></i>{{$teletext->views}}</span>
        </div>
    </div>
</a>
