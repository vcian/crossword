<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-r from-blue-100 via-white to-blue-100">
        <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg relative">
            <!-- Crossword Pattern Background -->
            <div class="absolute inset-0 grid grid-cols-8 gap-1 p-4 opacity-5">
                @for ($i = 0; $i < 64; $i++)
                    <div class="aspect-square border {{ $i % 3 == 0 ? 'bg-gray-800' : 'bg-white' }}"></div>
                @endfor
            </div>

            <!-- Logo and Title -->
            <div class="text-center mb-8 relative z-10">
                <h1 class="text-4xl font-bold text-gray-900 mb-2" style="font-family: 'Courier New', monospace;">
                    C R O S S W O R D
                </h1>
                <div class="grid grid-cols-11 gap-1 justify-center mb-4 mx-auto max-w-fit">
                    @foreach(str_split('PUZZLEMANIA') as $letter)
                        <div class="w-7 h-7 border-2 border-gray-800 flex items-center justify-center font-bold {{ $loop->iteration % 2 == 0 ? 'bg-gray-800 text-white' : 'bg-white text-gray-800' }} transform hover:scale-110 transition-transform duration-200">
                            {{ $letter }}
                        </div>
                    @endforeach
                </div>
                <p class="text-gray-600">Welcome back, puzzle solver!</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4 relative z-10" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6 relative z-10">
                @csrf

                <!-- Email Address -->
                <div class="bg-white/90 rounded-lg p-4 shadow-sm">
                    <x-input-label for="email" :value="__('Email')" class="text-gray-700" />
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <x-text-input id="email" class="block mt-1 w-full pl-10 bg-white" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="your@email.com" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between mt-4">
                    <!-- Register Link -->
                    <div class="text-sm">
                        <span class="text-gray-600">New to Crossword?</span>
                        <a href="{{ route('register') }}" class="ml-1 font-semibold text-indigo-600 hover:text-indigo-500 hover:underline">
                            Register here
                        </a>
                    </div>

                    <x-primary-button class="ml-3">
                        {{ __('Login') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .aspect-square {
        aspect-ratio: 1 / 1;
    }
    </style>
</body>
</html>
