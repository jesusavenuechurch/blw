@extends('layouts.app')

@section('title', 'Welcome to Angel Lounge')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-12 rounded-3xl shadow-2xl text-center max-w-md">
        <h1 class="text-4xl font-bold mb-6 text-purple-700">Welcome to Angel Lounge</h1>
        <p class="text-gray-600 mb-8">Your journey starts here. Login to access your dashboard.</p>
        <a href="{{ url('/admin') }}" 
           class="inline-block bg-gradient-to-r from-purple-600 via-blue-500 to-indigo-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:shadow-lg transform hover:scale-105 transition duration-300">
           Login
        </a>
    </div>
</div>
@endsection