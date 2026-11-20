@extends('errors.layout')
@section('code', '503')
@section('title', 'Back very soon')
@section('message', 'We\'re making a few improvements to the store. Please check back in a few minutes.')
@section('actions')
<a class="btn primary" href="{{ url('/') }}">Try again</a>
@endsection
