@extends('layouts.app') 

@section('title', 'Welcome to Angel Lounges')

@section('content')
<div class="flex items-center justify-center w-full py-16">
    <div class="glass-effect bg-white/80 p-12 rounded-3xl shadow-2xl text-center max-w-md mx-4">
        <h1 class="text-4xl font-bold mb-6 text-purple-700">Angel Lounge Guest Management Portal</h1>
        <a href="{{ url('/admin') }}" 
           class="inline-block bg-gradient-to-r from-purple-600 via-blue-500 to-indigo-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:shadow-lg transform hover:scale-105 transition duration-300">
           Login
        </a>
    </div>
</div>
@endsection