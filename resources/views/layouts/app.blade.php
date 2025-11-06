<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Partner Registration')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @yield('head')
</head>
<body class="min-h-screen flex flex-col" 
      style="background: linear-gradient(to bottom right, #4169E1, #27408B);">
    <style>
    /* Body background - royal theme */
    body {
        background: linear-gradient(to bottom right, #4169E1, #27408B);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* Glass effect - safe fallback for mobile */
    .glass-effect {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.3);
        /* Removed backdrop-filter to prevent crashes on Android */
    }

    /* Decorative blobs removed for mobile to reduce GPU load */
    .decorative-blob {
        display: none;
    }

    /* Optional: text gradient for headings */
    .text-gradient {
        background: linear-gradient(to right, #9b5de5, #4169E1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Responsive spacing */
    @media (max-width: 768px) {
        .glass-effect {
            padding: 0.5rem;
            border-radius: 0.5rem;
        }
    }
</style>

    <!-- Decorative Elements - Fixed positions, no animation -->
    <div class="decorative-blob blob-1"></div>
    <div class="decorative-blob blob-2"></div>
    <div class="decorative-blob blob-3"></div>

    <!-- Navigation -->
    <nav class="relative z-10 glass-effect shadow-xl">
        <div class="container mx-auto px-4 py-4 md:py-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2 md:space-x-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center">
                        {{-- <img src="{{ asset('images/logo2.png') }}" alt="Angel Lounges Logo" class="w-full h-full object-contain rounded-full shadow-md border border-white/40"> --}}
                    </div>
                    <h1 class="text-xl md:text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-500 bg-clip-text text-transparent">
                        Angel Lounges
                    </h1>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 py-8 md:py-12 flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    {{-- <footer class="relative z-10 glass-effect shadow-inner mt-auto">
        <div class="container mx-auto px-4 md:px-6 py-6 md:py-8">
            
            <!-- Mobile: Stacked Layout -->
            <div class="flex flex-col space-y-4 md:hidden">
                <!-- Logos Grid -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="Angel Lounge Logo"
                             class="w-10 h-10 object-contain rounded-full border border-white/40 shadow-md">
                        <span class="text-[10px] text-gray-600 mt-1 text-center leading-tight">Angel<br>Lounges</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <img src="{{ asset('images/ror.jpg') }}" alt="ROR Logo"
                             class="w-10 h-10 object-contain rounded-full border border-white/40 shadow-md">
                        <span class="text-[10px] text-gray-600 mt-1 text-center leading-tight">Rhapsody of<br>Realities</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <img src="{{ asset('images/ippc.jpeg') }}" alt="IPPC Logo"
                             class="w-10 h-10 object-contain rounded-full border border-white/40 shadow-md">
                        <span class="text-[10px] text-gray-600 mt-1 text-center leading-tight">IPPC<br>2025</span>
                    </div>
                </div>

                <!-- Copyright -->
                <p class="text-[10px] text-gray-700 font-medium text-center leading-tight">
                    &copy; {{ date('Y') }} Angel Lounges.<br>All Rights Reserved.
                </p>
            </div>

            <!-- Desktop: Horizontal Layout -->
            <div class="hidden md:flex items-center justify-between">
                
                <!-- Logo Section -->
                <div class="flex items-center space-x-6">
                    <!-- Angel Lounge Logo -->
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Angel Lounge Logo"
                             class="w-12 h-12 object-contain rounded-full border border-white/40 shadow-md">
                        <span class="text-gray-600 text-sm font-medium">Angel Lounges</span>
                    </div>

                    <!-- ROR Logo -->
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('images/ror.jpg') }}" alt="ROR Logo"
                             class="w-12 h-12 object-contain rounded-full border border-white/40 shadow-md">
                        <span class="text-gray-600 text-sm font-medium">Rhapsody of Realities</span>
                    </div>

                    <!-- IPPC Logo -->
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('images/ippc.jpeg') }}" alt="IPPC Logo"
                             class="w-12 h-12 object-contain rounded-full border border-white/40 shadow-md">
                        <span class="text-gray-600 text-sm font-medium">IPPC 2025</span>
                    </div>
                </div>

                <!-- Copyright -->
                <p class="text-sm text-gray-700 font-medium">
                    &copy; {{ date('Y') }} Angel Lounges. All Rights Reserved.
                </p>
            </div>
        </div>
    </footer> --}}
</body>
</html>