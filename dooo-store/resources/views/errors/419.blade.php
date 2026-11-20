@extends('errors.layout')
@section('code', '419')
@section('title', 'Page expired')
@section('message', 'For your security this page timed out. Please go back, refresh the page and try again.')
@section('actions')
<a class="btn primary" href="{{ url()->previous() }}">Go back</a>
@endsection
