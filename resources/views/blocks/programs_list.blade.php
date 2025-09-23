@foreach ($programs as $program)
    @include('blocks.program', ['show_channels' => true])
@endforeach
