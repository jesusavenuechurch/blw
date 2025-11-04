@extends('layouts.app')

@section('title', 'Partner Registration')

@section('head')
<style>
    .input-focus {
        transition: all 0.3s ease;
    }
    
    .input-focus:focus {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.3);
    }

    .hidden-field {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 max-w-5xl py-8">
    <!-- Error Message -->
    @if(session('error'))
        <div class="bg-white rounded-2xl shadow-2xl p-4 mb-6 border-l-4 border-red-500">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="font-bold text-red-800 text-lg">Error!</p>
                    <p class="text-red-700 mt-1">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="bg-white rounded-2xl shadow-2xl p-4 mb-6 border-l-4 border-red-500">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="font-bold text-red-800 text-lg">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-red-700 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Info Banner -->
    <div class="bg-white rounded-2xl shadow-lg p-4 mb-6 border-l-4 border-blue-500">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-gray-700">
                    <strong>Registering for:</strong> {{ $partner->email }}
                </p>
                <p class="text-sm text-gray-600 mt-1">This link can only be used once. Please complete your registration below.</p>
            </div>
        </div>
    </div>

    <!-- Registration Form Card -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 via-blue-500 to-indigo-600 p-8 text-white text-center">
            <svg class="w-20 h-20 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
            </svg>
            <h1 class="text-4xl font-extrabold mb-2">Complete Your Registration</h1>
            <p class="text-purple-100 text-lg">IPPC 2025 Partner Program</p>
        </div>

        <!-- Form Content -->
        <div class="p-8 md:p-12">
            <form method="POST" action="{{ route('partner.store', $token) }}" class="space-y-8">
                @csrf

                <!-- Personal Information -->
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Personal Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Designation <span class="text-red-500">*</span>
                            </label>
                            <select name="title" required class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-purple-500 @error('title') border-red-500 @enderror">
                                <option value="">Select Designation</option>
                                <option value="Non-Pastoring" {{ old('title', $partner->title) == 'Non-Pastoring' ? 'selected' : '' }}>Non-Pastoring</option>
                                <option value="Church Pastor" {{ old('title', $partner->title) == 'Church Pastor' ? 'selected' : '' }}>Church Pastor</option>
                                <option value="Sub-Group Pastor" {{ old('title', $partner->title) == 'Sub-Group Pastor' ? 'selected' : '' }}>Sub-Group Pastor</option>
                                <option value="Group Pastor" {{ old('title', $partner->title) == 'Group Pastor' ? 'selected' : '' }}>Group Pastor</option>
                                <option value="Asst. Zonal Pastor" {{ old('title', $partner->title) == 'Asst. Zonal Pastor' ? 'selected' : '' }}>Asst. Zonal Pastor</option>
                                <option value="Zonal Pastor" {{ old('title', $partner->title) == 'Zonal Pastor' ? 'selected' : '' }}>Zonal Pastor</option>
                                <option value="Zonal Director" {{ old('title', $partner->title) == 'Zonal Director' ? 'selected' : '' }}>Zonal Director</option>
                                <option value="Regional Pastor" {{ old('title', $partner->title) == 'Regional Pastor' ? 'selected' : '' }}>Regional Pastor</option>
                            </select>
                            @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" value="{{ old('full_name', $partner->full_name) }}" required placeholder="John Doe"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-purple-500 @error('full_name') border-red-500 @enderror">
                            @error('full_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" value="{{ $partner->email }}" readonly class="w-full border-2 border-gray-200 bg-gray-100 rounded-xl px-4 py-3.5 cursor-not-allowed">
                            <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone', $partner->phone) }}" placeholder="+1234567890"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-purple-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">KC Handle</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-semibold">@</span>
                            <input type="text" name="kc_handle" value="{{ old('kc_handle', $partner->kc_handle) }}" placeholder="username"
                                class="w-full border-2 border-gray-300 rounded-xl pl-10 pr-4 py-3.5 input-focus focus:border-purple-500">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Your KingsChat handle (optional)</p>
                    </div>
                </div>

                <!-- Church Information -->
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Church Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Region</label>
                            <input type="text" name="region" value="{{ old('region', $partner->region) }}" placeholder="Enter your region"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Zone</label>
                            <input type="text" name="zone" value="{{ old('zone', $partner->zone) }}" placeholder="Enter your zone"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Group</label>
                            <input type="text" name="group" value="{{ old('group', $partner->group) }}" placeholder="Enter your group"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Church</label>
                            <input type="text" name="church" value="{{ old('church', $partner->church) }}" placeholder="Enter your church name"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Partnership Details -->
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Partnership Details</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">ROR Copies Sponsored</label>
                            <input type="number" name="ror_copies_sponsored" value="{{ old('ror_copies_sponsored', $partner->ror_copies_sponsored ?? 0) }}" min="0"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Partnership Tier <span class="ml-2 text-xs font-normal text-gray-500">(Assigned by admin)</span>
                            </label>
                            <div class="w-full border-2 border-gray-200 bg-gray-100 rounded-xl px-4 py-3.5 font-semibold">
                                @if($partner->tier === 'ruby')
                                    <span class="text-red-600">💎 Ruby Partner</span>
                                @elseif($partner->tier === 'silver')
                                    <span class="text-gray-600">🥈 Silver Partner</span>
                                @elseif($partner->tier === 'gold')
                                    <span class="text-yellow-600">🥇 Gold Partner</span>
                                @elseif($partner->tier === 'diamond')
                                    <span class="text-cyan-600">💠 Diamond Partner</span>
                                @else
                                    <span class="text-gray-600">Not Assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="relative flex items-start p-5 bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl cursor-pointer hover:shadow-lg transition duration-300">
                            <input type="checkbox" name="will_attend_ippc" id="will_attend_ippc" value="1" 
                                   {{ old('will_attend_ippc', $partner->will_attend_ippc) ? 'checked' : '' }}
                                   onchange="toggleIPPCFields()"
                                   class="w-5 h-5 text-purple-600 rounded mt-0.5">
                            <div class="ml-4">
                                <span class="font-semibold text-gray-800 block">Will you attend IPPC 2025?</span>
                                <span class="text-sm text-gray-600">Check if you will attend the International Partners and Pastors Conference</span>
                            </div>
                        </label>

                        <div id="exhibition_field" class="hidden-field">
                            <label class="relative flex items-start p-5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl cursor-pointer hover:shadow-lg transition duration-300">
                                <input type="checkbox" name="will_be_at_exhibition" value="1" 
                                       {{ old('will_be_at_exhibition', $partner->will_be_at_exhibition) ? 'checked' : '' }}
                                       class="w-5 h-5 text-blue-600 rounded mt-0.5">
                                <div class="ml-4">
                                    <span class="font-semibold text-gray-800 block">Will you be at the ROR exhibition at Angel Court?</span>
                                    <span class="text-sm text-gray-600">Check if you plan to attend the exhibition</span>
                                </div>
                            </label>
                        </div>

                        <div id="delivery_field" class="hidden-field">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                How should we deliver your ROR gifts? <span class="text-red-500">*</span>
                            </label>
                            <textarea name="delivery_method" id="delivery_method" rows="3" placeholder="Please provide details on how you'd like to receive your ROR materials..."
                                      class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-indigo-500">{{ old('delivery_method', $partner->delivery_method) }}</textarea>
                            <p class="text-sm text-gray-500 mt-1">Required if not attending IPPC</p>
                        </div>
                    </div>
                </div>

                <!-- Spouse Information -->
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Spouse Information</h2>
                    </div>

                    <div class="mb-6">
                        <label class="relative flex items-start p-5 bg-gradient-to-r from-pink-50 to-purple-50 rounded-xl cursor-pointer hover:shadow-lg transition duration-300">
                            <input type="checkbox" name="coming_with_spouse" id="coming_with_spouse" value="1" 
                                   {{ old('coming_with_spouse', $partner->coming_with_spouse) ? 'checked' : '' }}
                                   onchange="toggleSpouseFields()"
                                   class="w-5 h-5 text-pink-600 rounded mt-0.5">
                            <div class="ml-4">
                                <span class="font-semibold text-gray-800 block">Coming with spouse?</span>
                                <span class="text-sm text-gray-600">Check if your spouse will be joining you</span>
                            </div>
                        </label>
                    </div>

                    <div id="spouse_fields" class="hidden-field">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Spouse Title <span class="text-red-500">*</span>
                                </label>
                                <select name="spouse_title" id="spouse_title" class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-pink-500">
                                    <option value="">Select Title</option>
                                    <option value="Bro" {{ old('spouse_title', $partner->spouse_title) == 'Bro' ? 'selected' : '' }}>Bro</option>
                                    <option value="Sis" {{ old('spouse_title', $partner->spouse_title) == 'Sis' ? 'selected' : '' }}>Sis</option>
                                    <option value="Dcn" {{ old('spouse_title', $partner->spouse_title) == 'Dcn' ? 'selected' : '' }}>Dcn</option>
                                    <option value="Pastor" {{ old('spouse_title', $partner->spouse_title) == 'Pastor' ? 'selected' : '' }}>Pastor</option>
                                    <option value="Mr" {{ old('spouse_title', $partner->spouse_title) == 'Mr' ? 'selected' : '' }}>Mr</option>
                                    <option value="Mrs" {{ old('spouse_title', $partner->spouse_title) == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                    <option value="Miss" {{ old('spouse_title', $partner->spouse_title) == 'Miss' ? 'selected' : '' }}>Miss</option>
                                    <option value="Dr" {{ old('spouse_title', $partner->spouse_title) == 'Dr' ? 'selected' : '' }}>Dr</option>
                                    <option value="Rev" {{ old('spouse_title', $partner->spouse_title) == 'Rev' ? 'selected' : '' }}>Rev</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Spouse First Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="spouse_name" id="spouse_name" value="{{ old('spouse_name', $partner->spouse_name) }}" placeholder="First name"
                                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-pink-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Spouse Surname <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="spouse_surname" id="spouse_surname" value="{{ old('spouse_surname', $partner->spouse_surname) }}" placeholder="Surname"
                                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-pink-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Spouse KC Handle</label>
                                <input type="text" name="spouse_kc_handle" value="{{ old('spouse_kc_handle', $partner->spouse_kc_handle) }}" placeholder="@username"
                                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-pink-500">
                                <p class="text-xs text-gray-500 mt-1">Optional: Spouse's KingsChat handle</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-purple-600 via-blue-500 to-indigo-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:shadow-2xl transform hover:scale-105 transition duration-300">
                        <span class="flex items-center justify-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Complete Registration
                        </span>
                    </button>
                    <p class="text-center text-sm text-gray-500 mt-4">
                        By submitting, you confirm that all information provided is accurate.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleIPPCFields() {
        const attendingIPPC = document.getElementById('will_attend_ippc').checked;
        const exhibitionField = document.getElementById('exhibition_field');
        const deliveryField = document.getElementById('delivery_field');
        const deliveryTextarea = document.getElementById('delivery_method');
        
        if (attendingIPPC) {
            exhibitionField.classList.remove('hidden-field');
            deliveryField.classList.add('hidden-field');
            if (deliveryTextarea) deliveryTextarea.required = false;
        } else {
            exhibitionField.classList.add('hidden-field');
            deliveryField.classList.remove('hidden-field');
            if (deliveryTextarea) deliveryTextarea.required = true;
        }
    }

    function toggleSpouseFields() {
        const withSpouse = document.getElementById('coming_with_spouse').checked;
        const spouseFields = document.getElementById('spouse_fields');
        const spouseTitle = document.getElementById('spouse_title');
        const spouseName = document.getElementById('spouse_name');
        const spouseSurname = document.getElementById('spouse_surname');
        
        if (withSpouse) {
            spouseFields.classList.remove('hidden-field');
            if (spouseTitle) spouseTitle.required = true;
            if (spouseName) spouseName.required = true;
            if (spouseSurname) spouseSurname.required = true;
        } else {
            spouseFields.classList.add('hidden-field');
            if (spouseTitle) spouseTitle.required = false;
            if (spouseName) spouseName.required = false;
            if (spouseSurname) spouseSurname.required = false;
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleIPPCFields();
        toggleSpouseFields();
    });
</script>
@endsection