@extends('LaravelJwtAuth::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>
        This view is loaded from plugin: {!! config('laravel-jwt-auth.name') !!}
    </p>
@endsection
