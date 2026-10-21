@extends('layouts.app-with-header')
@section('main')
 <div class="relative w-full min-h-screen">
  <form action="{{ route('operation.start') }}" method="POST">
  @csrf
    <img
        src="{{ asset('img/homegbdesign.png') }}"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 w-full h-full object-cover -z-10 pointer-events-none"
    >
    <div class="relative pt-14 pb-6">
        @include('partials.operation-guide')
    </div>
    <div class="relative flex justify-center pb-10">
        <button type="submit"
                class="border border-green-400 rounded-md text-gray-600 px-4 py-2 hover:bg-green-50 transition-colors cursor-pointer">
                作業を始める
        </button>
    </div>
    </form>
 </div>
@endsection
