@extends('backpack::layouts.top_left')

@section('after_styles')
    @livewireStyles
@endsection

@section('content')

    <h2>{{ $entry->name }}</h2>

    <livewire:owner-forms-table/>


    {{--    TODO: Include page for owners (teams/users etc) to manage forms + enumerator accounts     --}}
@endsection


@section('after_scripts')
    @livewireScripts
@endsection
