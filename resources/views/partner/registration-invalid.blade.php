@extends('layouts.app')

@section('title', 'Registration Error')

@section('content')
<div class="container mx-auto px-4 max-w-2xl">
    <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden text-center p-12">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Registration Unavailable</h1>
        <p class="text-gray-600 text-lg mb-8">{{ $message }}</p>
        
        @isset($partner)
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 text-left">
                <p class="text-sm text-blue-700">
                    <strong>Registration Details:</strong><br>
                    Name: {{ $partner->full_name }}<br>
                    Email: {{ $partner->email }}<br>
                    Registered on: {{ $partner->token_used_at?->format('M d, Y H:i') }}
                </p>
            </div>
        @endisset
        
        <a href="mailto:support@angel-lounge.com" class="inline-block bg-gradient-to-r from-purple-600 to-blue-500 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transform hover:scale-105 transition duration-300">
            Contact Support
        </a>
    </div>
</div>
@endsection