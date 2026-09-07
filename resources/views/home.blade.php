@extends('layouts.app-with-header')

@section('main')
 <div class="relative w-full min-h-screen">
    <img
        src="{{ asset('img/homegbdesign.png') }}"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 w-full h-full object-cover -z-10 pointer-events-none"
    >
    <div class="relative grid grid-cols-1 place-items-center gap-2 pt-14">
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
 </div>
@endsection
