@extends('layouts.app-with-header')

@section('main')  
 <div class="w-full p-2">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-2 w-full">
        <div class="lg:col-span-2 p-2">
            @include('partials.workcountcard')
        </div>
        <div class="lg:col-span-3 p-2">
            @include('partials.taskcountcard')
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-2 w-full mt-3">
        <div class="lg:col-span-2 p-2">
            @include('partials.goalscard')
        </div>
        <div class="lg:col-span-3 p-2">
            @include('partials.dailychart')
        </div>
    </div>
 </div>
@endsection


