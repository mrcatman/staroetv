@foreach ($programs as $program)
    @include('blocks.programs.item', ['show_channels' => true])
@endforeach
