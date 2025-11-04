<div class="space-y-6 p-2">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Email Campaign Statistics</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overview of partner email delivery status</p>
        </div>
        <div class="text-gray-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
    </div>

    <!-- Main Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Total Partners -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-5 border border-blue-200 dark:border-blue-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide">Total Partners</p>
                    <p class="text-4xl font-bold text-blue-700 dark:text-blue-300 mt-2">{{ $stats['total'] }}</p>
                    <p class="text-xs text-blue-600/70 dark:text-blue-400/70 mt-1">Registered in system</p>
                </div>
                <div class="text-blue-500 opacity-20">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Emails Sent -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-5 border border-green-200 dark:border-green-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wide">Successfully Sent</p>
                    <p class="text-4xl font-bold text-green-700 dark:text-green-300 mt-2">{{ $stats['sent'] }}</p>
                    @if($stats['total'] > 0)
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex-1 bg-green-200 dark:bg-green-900/40 rounded-full h-2">
                                <div class="bg-green-600 dark:bg-green-500 h-2 rounded-full transition-all" style="width: {{ ($stats['sent'] / $stats['total']) * 100 }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-green-700 dark:text-green-400">
                                {{ round(($stats['sent'] / $stats['total']) * 100, 1) }}%
                            </span>
                        </div>
                    @endif
                </div>
                <div class="text-green-500 opacity-20">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Emails Failed -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-5 border border-red-200 dark:border-red-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide">Failed to Send</p>
                    <p class="text-4xl font-bold text-red-700 dark:text-red-300 mt-2">{{ $stats['failed'] }}</p>
                    @if($stats['total'] > 0)
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex-1 bg-red-200 dark:bg-red-900/40 rounded-full h-2">
                                <div class="bg-red-600 dark:bg-red-500 h-2 rounded-full transition-all" style="width: {{ ($stats['failed'] / $stats['total']) * 100 }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-red-700 dark:text-red-400">
                                {{ round(($stats['failed'] / $stats['total']) * 100, 1) }}%
                            </span>
                        </div>
                    @endif
                </div>
                <div class="text-red-500 opacity-20">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Emails Pending -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 rounded-xl p-5 border border-yellow-200 dark:border-yellow-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-yellow-600 dark:text-yellow-400 uppercase tracking-wide">Pending in Queue</p>
                    <p class="text-4xl font-bold text-yellow-700 dark:text-yellow-300 mt-2">{{ $stats['pending'] }}</p>
                    @if($stats['total'] > 0)
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex-1 bg-yellow-200 dark:bg-yellow-900/40 rounded-full h-2">
                                <div class="bg-yellow-600 dark:bg-yellow-500 h-2 rounded-full transition-all animate-pulse" style="width: {{ ($stats['pending'] / $stats['total']) * 100 }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-yellow-700 dark:text-yellow-400">
                                {{ round(($stats['pending'] / $stats['total']) * 100, 1) }}%
                            </span>
                        </div>
                    @endif
                </div>
                <div class="text-yellow-500 opacity-20">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Not Sent Section -->
    @if($stats['not_sent'] > 0)
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl p-5 border-2 border-dashed border-gray-300 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Not Sent Yet</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-2">{{ $stats['not_sent'] }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Partners without email attempts</p>
                </div>
                <div class="text-gray-400 opacity-50">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Section -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Quick Summary</h4>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Delivery Rate:</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    @if($stats['total'] > 0)
                        {{ round((($stats['sent']) / $stats['total']) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Emails Processed:</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $stats['sent'] + $stats['failed'] }} / {{ $stats['total'] }}
                </span>
            </div>
            @if($stats['failed'] > 0)
                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-red-600 dark:text-red-400 font-medium">⚠️ Attention Required:</span>
                    <span class="font-bold text-red-700 dark:text-red-400">
                        {{ $stats['failed'] }} failed email(s)
                    </span>
                </div>
            @endif
            @if($stats['pending'] > 0)
                <div class="flex justify-between items-center">
                    <span class="text-yellow-600 dark:text-yellow-400 font-medium">⏳ In Progress:</span>
                    <span class="font-bold text-yellow-700 dark:text-yellow-400">
                        {{ $stats['pending'] }} pending email(s)
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer Note -->
    <div class="text-center text-xs text-gray-500 dark:text-gray-400 pt-2">
        <p>Statistics are updated in real-time. Refresh the page to see the latest data.</p>
    </div>
</div>