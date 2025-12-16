<a href="{{$package->full_url}}" class="interprogram-package">
    <div class="interprogram-package__cover" data-pictures-count="{{$package->cover ? 1 : count($package->random_pictures)}}">
        @if (($package->cover))
            <div class="interprogram-package__cover__picture interprogram-package__cover__picture--big" style="background-image: url({{$package->cover}})"></div>
        @else
            @foreach ($package->random_pictures as $picture)
                <div class="interprogram-package__cover__picture" style="background-image: url({{$picture}})"></div>
            @endforeach
        @endif
    </div>
    <div class="interprogram-package__name">{{$package->name}}</div>
    <div class="interprogram-package__years">{{$package->years_range}}</div>
</a>
