@extends('layouts.app')

@section('title', 'Registration Successful')

@section('content')
<div class="container mx-auto px-4 max-w-2xl">
    <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden text-center p-12">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 success-checkmark">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Registration Complete!</h1>
        <p class="text-gray-600 text-lg mb-8">
            Thank you for completing your registration, <strong>{{ $partner->full_name }}</strong>!
        </p>

{{-- ✅ QR Code Section --}}
@if (!empty($partner->qr_code_path))
    <div class="mb-10">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your QR Code</h2>
        <div class="flex justify-center mb-6">
            <img 
                src="{{ asset('storage/' . $partner->qr_code_path) }}" 
                alt="Your QR Code" 
                class="w-48 h-48 object-contain border-4 border-green-100 rounded-xl shadow-md"
                id="partner-qr"
            >
        </div>

        {{-- Partner Details --}}
        <div class="bg-blue-50 rounded-xl p-6 mb-6 text-left text-gray-700 space-y-2 shadow-inner">
            <p><strong>Title:</strong> {{ $partner->title }}</p>
            <p><strong>Name:</strong> {{ $partner->full_name }}</p>
            <p><strong>Tier:</strong> {{ $partner->tier ?? 'N/A' }}</p>
            <p><strong>ROR Copies Sponsored:</strong> {{ $partner->ror_copies_sponsored ?? 0 }}</p>
        </div>

        {{-- Download Button --}}
        <div class="flex justify-center">
            <a href="{{ asset('storage/' . $partner->qr_code_path) }}" 
               download="QR_{{ $partner->full_name }}.png"
               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200">
               Download QR Code
            </a>
        </div>

        <p class="text-gray-500 text-sm mt-3 text-center">
            Please save this QR code — you will need it for event check-in.
        </p>
    </div>
@endif
        
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl p-6 mb-8">
            <h2 class="font-bold text-gray-800 mb-4">What's Next?</h2>
            <ul class="text-left space-y-2 text-gray-700">
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>You will receive a confirmation email with your QR code</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Please Save your QR code, to be presented for Lounge Access.</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>We'll send you event updates and reminders</span>
                </li>
            </ul>
        </div>
        
        <p class="text-sm text-gray-500">
            Registration completed on {{ now()->format('F d, Y \a\t H:i') }}
        </p>
    </div>
</div>
@endsection