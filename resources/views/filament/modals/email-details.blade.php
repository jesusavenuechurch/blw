<div class="space-y-4">
    <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
        <h3 class="text-lg font-semibold mb-3">Email Details for {{ $partner->full_name }}</h3>
        
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="font-medium text-gray-700 dark:text-gray-300">Email Address:</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $partner->email }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                <span>
                    @if($partner->email_sent)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✓ Sent
                        </span>
                    @elseif($partner->email_failed)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            ✗ Failed
                        </span>
                    @elseif($partner->email_pending)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            ⏳ Pending
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Not Sent
                        </span>
                    @endif
                </span>
            </div>

            <div class="flex justify-between">
                <span class="font-medium text-gray-700 dark:text-gray-300">Registered:</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $partner->created_at->format('M d, Y h:i A') }}</span>
            </div>

            @if($partner->email_response)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Response/Error Message:</span>
                    <div class="mt-2 p-3 bg-white dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                        <code class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-all">{{ $partner->email_response }}</code>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>