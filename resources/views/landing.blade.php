@extends('layout.app')
@section('title')
My Courses
@endsection

@section('content')
<style>
    .bannerhe1 {
        line-height: 1.5
    }
</style>
    <div class="flex w-full">
        <div class="banner">
            <div class="flex">
                <div class=" px-32 py-16">
                    <h1 class="bannerhe1 text-6xl font-bold lin">MOVE BEYOND <br> THE LIMITATIONS OF <br> E-LEARNING</h1>
                    <h2 class=" text-2xl text-gray-500">Anytime, anywhere to discover yourself.</h2>
                    <button class=" bg-text py-6 outline-none px-32 rounded-md mb-6 text-white font-bold text-xl cursor-pointer mt-6">Get started</button>
                </div>
                <div class="text-right">
                    <img class="ml-20" src="{{ asset('png/banner.png') }}" alt="">
                </div>
            </div>
        </div>
    </div>
@endsection