@extends('layouts.default', ['vue' => true])
@section('content')
    <records-search
        :results='@json($results)'
        :counts='@json($counts)'
        :params='@json($params)'
        :periods='@json($periods)'
        :recommended-programs='@json($programs)'
        :commercials="@json($is_commercials_search)"
    >
    </records-search>
@endsection
