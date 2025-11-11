<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Download Check-in Report</h3>
            
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Start Date -->
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Start Date
                    </label>
                    <input 
                        type="date" 
                        id="startDate"
                        wire:model="startDate"
                        class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-yellow-500 focus:ring-yellow-500 px-3 py-2"
                    />
                </div>

                <!-- End Date -->
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        End Date
                    </label>
                    <input 
                        type="date" 
                        id="endDate"
                        wire:model="endDate"
                        class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-yellow-500 focus:ring-yellow-500 px-3 py-2"
                    />
                </div>
            </div>

            <!-- Info Text -->
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Generate an Excel report of all check-ins between the selected dates.
            </p>

            <!-- Download Button -->
            <button
                wire:click="downloadReport"
                class="inline-flex items-center gap-2 rounded-lg bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-500 dark:hover:bg-yellow-600 px-4 py-2 text-sm font-semibold text-white transition-colors"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16v-4m0 0V8m0 4H8m4 0h4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Download Excel Report
            </button>

            <!-- Summary Info -->
            <div class="mt-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-3 text-sm text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800">
                <p>📊 Report includes: Partner name, tier, check-in time, spouse status, and registration date.</p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>