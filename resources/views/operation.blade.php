@extends('layouts.app-with-header')
@section('main')
 <div class="relative w-full min-h-screen">
    <img
        src="{{ asset('img/homegbdesign.png') }}"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 w-full h-full object-cover -z-10 pointer-events-none"
    >
    <div class="relative pt-14 pb-6">
        @include('partials.operation-guide')
    </div>
 </div>
@endsection
