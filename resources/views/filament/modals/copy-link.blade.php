{{-- resources/views/filament/modals/copy-link.blade.php --}}

<div class="space-y-4 p-2">
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">
                    Partner Registration Link
                </h3>
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    Share this link with the partner to complete their registration. This link is unique and will expire once used.
                </p>
            </div>
        </div>
    </div>

    <div class="relative">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Registration URL
        </label>
        <div class="flex gap-2">
            <input 
                type="text" 
                id="registration-url" 
                value="{{ $url }}" 
                readonly 
                class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 font-mono"
                onclick="this.select()"
            >
            <button 
                type="button"
                onclick="copyToClipboard()"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span id="copy-btn-text">Copy</span>
            </button>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Click the input field to select all, or use the Copy button.
        </p>
    </div>

    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
                <p class="text-xs font-semibold text-yellow-900 dark:text-yellow-100 mb-1">
                    Important Security Note
                </p>
                <ul class="text-xs text-yellow-800 dark:text-yellow-200 space-y-1">
                    <li>• This link should only be shared with the registered partner</li>
                    <li>• The link will expire once the partner completes registration</li>
                    <li>• Do not share this link publicly or with unauthorized persons</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between pt-2">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span class="text-xs">Secure Link</span>
        </div>
        <div id="copy-success" class="hidden text-sm font-medium text-green-600 dark:text-green-400 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Copied!
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const input = document.getElementById('registration-url');
    const btnText = document.getElementById('copy-btn-text');
    const success = document.getElementById('copy-success');
    
    // Select and copy
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        
        // Show success feedback
        btnText.textContent = 'Copied!';
        success.classList.remove('hidden');
        
        // Reset after 3 seconds
        setTimeout(() => {
            btnText.textContent = 'Copy';
            success.classList.add('hidden');
        }, 3000);
        
    } catch (err) {
        console.error('Failed to copy:', err);
        alert('Failed to copy. Please select and copy manually.');
    }
}
</script>