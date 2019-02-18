@extends('layout.tpl')

@section('main-content')

<h1>Oops... Something went wrong!</h1>

<hr>

@if(isset($html))

<div style="border:3px solid red; padding:3px">{{ $html }}</div>

@else

<h3>{{ $message }}</h3>

@endif

<hr>

<br>

<a href="?" class="btn btn-primary" onclick="window.close(); return false;">Close</a><br>

<br>

@stop
