@extends('codesnoutr::layouts.app')

@section('title', 'Scan Results - CodeSnoutr')

@section('content')
    @livewire('codesnoutr-scan-results', ['scanId' => $scan->id])
@endsection
