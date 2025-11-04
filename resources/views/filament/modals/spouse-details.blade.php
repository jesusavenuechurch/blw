<div class="space-y-6 p-2">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Spouse Information</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Partner is coming with their spouse</p>
        </div>
        <div class="text-purple-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
    </div>

    <!-- Partner Info Card -->
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-5 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-lg">
                {{ strtoupper(substr($partner->full_name, 0, 1)) }}
            </div>
            <div>
                <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide">Primary Partner</p>
                <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ $partner->title }} {{ $partner->full_name }}</p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-blue-600 dark:text-blue-400 font-medium">Email</p>
                <p class="text-blue-900 dark:text-blue-100">{{ $partner->email }}</p>
            </div>
            @if($partner->phone)
                <div>
                    <p class="text-blue-600 dark:text-blue-400 font-medium">Phone</p>
                    <p class="text-blue-900 dark:text-blue-100">{{ $partner->phone }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Spouse Info Card -->
    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl p-5 border border-purple-200 dark:border-purple-800">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-purple-500 flex items-center justify-center text-white font-bold text-lg">
                {{ strtoupper(substr($partner->spouse_name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <p class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wide">Spouse Details</p>
                <p class="text-lg font-bold text-purple-900 dark:text-purple-100">
                    {{ $partner->spouse_title }} {{ $partner->spouse_name }} {{ $partner->spouse_surname }}
                </p>
            </div>
        </div>

        <div class="space-y-3">
            <!-- Title -->
            <div class="flex items-start justify-between py-2 border-b border-purple-200 dark:border-purple-700">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">Title</span>
                </div>
                <span class="text-sm font-bold text-purple-900 dark:text-purple-100">{{ $partner->spouse_title }}</span>
            </div>

            <!-- First Name -->
            <div class="flex items-start justify-between py-2 border-b border-purple-200 dark:border-purple-700">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">First Name</span>
                </div>
                <span class="text-sm font-bold text-purple-900 dark:text-purple-100">{{ $partner->spouse_name }}</span>
            </div>

            <!-- Surname -->
            <div class="flex items-start justify-between py-2 border-b border-purple-200 dark:border-purple-700">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">Surname</span>
                </div>
                <span class="text-sm font-bold text-purple-900 dark:text-purple-100">{{ $partner->spouse_surname }}</span>
            </div>

            <!-- KC Handle -->
            @if($partner->spouse_kc_handle)
                <div class="flex items-start justify-between py-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                        <span class="text-sm font-medium text-purple-700 dark:text-purple-300">KingsChat Handle</span>
                    </div>
                    <span class="text-sm font-bold text-purple-900 dark:text-purple-100">{{ $partner->spouse_kc_handle }}</span>
                </div>
            @else
                <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                    <p class="text-xs text-purple-700 dark:text-purple-300 text-center italic">
                        No KingsChat handle provided
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Additional Info -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Additional Information
        </h4>
        
        <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Partnership Tier:</span>
                <span class="font-semibold">
                    @if($partner->tier === 'ruby')
                        <span class="text-red-600 dark:text-red-400">💎 Ruby</span>
                    @elseif($partner->tier === 'silver')
                        <span class="text-gray-600 dark:text-gray-400">🥈 Silver</span>
                    @elseif($partner->tier === 'gold')
                        <span class="text-yellow-600 dark:text-yellow-400">🥇 Gold</span>
                    @elseif($partner->tier === 'diamond')
                        <span class="text-cyan-600 dark:text-cyan-400">💠 Diamond</span>
                    @endif
                </span>
            </div>

            @if($partner->region)
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Region:</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $partner->region }}</span>
                </div>
            @endif

            @if($partner->church)
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Church:</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $partner->church }}</span>
                </div>
            @endif

            <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Will Attend IPPC 2025:</span>
                <span class="font-semibold">
                    @if($partner->will_attend_ippc)
                        <span class="text-green-600 dark:text-green-400">✓ Yes</span>
                    @else
                        <span class="text-red-600 dark:text-red-400">✗ No</span>
                    @endif
                </span>
            </div>

            @if($partner->will_attend_ippc && $partner->will_be_at_exhibition)
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">At Exhibition:</span>
                    <span class="font-semibold text-green-600 dark:text-green-400">✓ Yes</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer Note -->
    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-200 dark:border-purple-700">
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-purple-700 dark:text-purple-300">
                Both partners will be included in the IPPC 2025 registration. Make sure all contact details are accurate.
            </p>
        </div>
    </div>
</div>