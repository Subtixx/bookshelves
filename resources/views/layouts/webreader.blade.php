@extends('layouts.default', ['title' => 'Webreader, to read eBooks in your browser', 'route' => 'webreader.index',
'slideover' => true])

@section('styles')
    <link rel="stylesheet" href="{{ mix('assets/css/blade/wiki.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/css/blade/markdown.css') }}">
@endsection

@section('content')
    <div class="prose prose-lg dark:prose-light">
        @yield('webreader')
    </div>
@endsection
