@if ($getState())
    <div class="p-4 flex flex-col items-center">
        <img 
            src="{{ asset('storage/' . $getState()) }}" 
            alt="QR Code" 
            class="rounded-lg shadow-md w-40 h-40 object-contain"
        >
        <p class="text-xs text-gray-500 mt-2">Scan to verify</p>
    </div>
@else
    <p class="text-sm text-gray-400 italic">QR code not generated yet.</p>
@endif