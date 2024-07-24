@extends('mail::message')

@section('content')
    <h1>Hello, {{ $name }}!</h1>
    <p>{{ $htmlContent }}</p>
@endsection

@section('subcopy')
    @if (isset($subcopy))
        {{ $subcopy }}
    @endif
@endsection