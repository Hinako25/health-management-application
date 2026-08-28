@extends('layouts.app-with-header')
@section('main')
 <div class="relative w-full min-h-screen dark:bg-black">
    <img
        src="{{ asset('img/homegbdesign.png') }}"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 w-full h-full object-cover -z-10 pointer-events-none dark:hidden"
    >
    <div class="relative pt-14">
        @include('partials.dashboard-settings')
    </div>
 </div>
@endsection
