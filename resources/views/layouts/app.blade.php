<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Partner Registration')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @yield('head')
</head>
<body class="min-h-screen" style="background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe); background-size: 400% 400%; animation: gradient 15s ease infinite;">
    <style>
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>

    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-30" style="animation: float 6s ease-in-out infinite;"></div>
        <div class="absolute top-40 right-10 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-30" style="animation: float 6s ease-in-out infinite; animation-delay: 2s;"></div>
        <div class="absolute -bottom-8 left-1/2 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-30" style="animation: float 6s ease-in-out infinite; animation-delay: 4s;"></div>
    </div>

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>

    <!-- Navigation -->
    <nav class="relative z-10 glass-effect shadow-xl">
        <div class="container mx-auto px-4 py-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br rounded-lg flex items-center justify-center">
                        <div class="w-12 h-12 flex items-center justify-center">
                            <img src="{{ asset('images/logo2.png') }}" alt="Angel Lounges Logo" class="w-12 h-12 object-contain rounded-full shadow-md border border-white/40">
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-500 bg-clip-text text-transparent">
                        Angel Lounges
                    </h1>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 py-12">
        @yield('content')
    </main>
    <!-- Footer -->
<footer class="relative z-10 glass-effect mt-12 shadow-inner">
    <div class="container mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        
        <!-- Logo Section -->
        <div class="flex items-center space-x-6">
            <!-- Angel Lounge Logo -->
            <div class="flex items-center space-x-3">
                <img src="{{ asset('images/logo.png') }}" alt="Angel Lounge Logo"
                     class="w-12 h-12 object-contain rounded-full border border-white/40 shadow-md">
                <h2 class="text-xl font-bold bg-gradient-to-r from-yellow-400 to-yellow-600 bg-clip-text text-transparent">
                    Angel Lounges
                </h2>
            </div>

            <!-- ROR Placeholder -->
            <div class="flex items-center space-x-2">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-semibold">
                <img src="{{ asset('images/ror.jpg') }}" alt="ROR Logo"
                     class="w-12 h-12 object-contain rounded-full border border-white/40 shadow-md">     
                </div>
                <span class="hidden md:inline text-gray-600 text-sm font-medium">Rhapsody of Realities</span>
            </div>

            <!-- IPPC Placeholder -->
            <div class="flex items-center space-x-2">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-semibold">
                    <img src="{{ asset('images/ippc.jpeg') }}" alt="IPPC Logo"
                     class="w-12 h-12 object-contain rounded-full border border-white/40 shadow-md">         
                </div>
                <span class="hidden md:inline text-gray-600 text-sm font-medium">IPPC 2025</span>
            </div>
        </div>

        <!-- Copyright -->
        <p class="text-sm text-gray-700 font-medium text-center md:text-left">
            &copy; {{ date('Y') }} Angel Lounges. All Rights Reserved.
        </p>

        <!-- Socials -->
        <div class="flex space-x-4">
            {{-- <a href="#" class="text-gray-600 hover:text-yellow-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M22.46 6c-.77.35-1.6.59-2.46.69a4.15 4.15 0 0 0 1.82-2.29 8.3 8.3 0 0 1-2.63 1 4.14 4.14 0 0 0-7 3.77A11.76 11.76 0 0 1 3.15 4.6a4.12 4.12 0 0 0 1.28 5.52A4.1 4.1 0 0 1 2.8 9v.05a4.14 4.14 0 0 0 3.32 4.06 4.16 4.16 0 0 1-1.86.07 4.15 4.15 0 0 0 3.87 2.87A8.33 8.33 0 0 1 2 19.54a11.75 11.75 0 0 0 6.29 1.84c7.55 0 11.68-6.26 11.68-11.68v-.53A8.3 8.3 0 0 0 22.46 6z"/>
                </svg>
            </a>
            <a href="#" class="text-gray-600 hover:text-yellow-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 0h-14c-2.76 0-5 2.24-5 5v14c0 2.75 2.24 5 5 5h7v-9h-3v-3h3v-2.3c0-3.1 1.9-4.8 4.7-4.8 1.3 0 2.4.1 2.7.1v3.2h-1.9c-1.5 0-1.8.7-1.8 1.7v2.1h3.6l-.5 3h-3.1v9h6c2.76 0 5-2.25 5-5v-14c0-2.76-2.24-5-5-5z"/>
                </svg>
            </a> --}}
        </div>
    </div>
</footer>
</body>
</html>