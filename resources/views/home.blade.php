@extends('layouts.app-with-header')

@section('main')  
 <div class="relative w-full min-h-[calc(100vh-5rem)]">
    <img
        src="{{ asset('img/homegbdesign.png') }}"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 w-full h-full object-cover -z-10 pointer-events-none"
    >
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-2 w-full mt-2">
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
