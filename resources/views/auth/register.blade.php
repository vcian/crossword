<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
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
<body class="font-sans antialiased h-full">
    <div class="min-h-full flex flex-col justify-center items-center bg-gradient-to-r from-blue-100 via-white to-blue-100">
        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg relative my-8">
            <!-- Crossword Pattern Background -->
            <div class="absolute inset-0 grid grid-cols-8 gap-1 p-4 opacity-5">
                @for ($i = 0; $i < 64; $i++)
                    <div class="aspect-square border {{ $i % 3 == 0 ? 'bg-gray-800' : 'bg-white' }}"></div>
                @endfor
            </div>

            <!-- Logo and Title -->
            <div class="text-center mb-8 relative z-10">
                <h1 class="text-3xl font-bold text-gray-900 mb-2" style="font-family: 'Courier New', monospace;">
                    C R O S S W O R D
                </h1>
                <div class="grid grid-cols-8 gap-1 justify-center mb-4 mx-auto max-w-fit">
                    @foreach(str_split('REGISTER') as $letter)
                        <div class="w-7 h-7 border-2 border-gray-800 flex items-center justify-center font-bold {{ $loop->iteration % 2 == 0 ? 'bg-gray-800 text-white' : 'bg-white text-gray-800' }} transform hover:scale-110 transition-transform duration-200">
                            {{ $letter }}
                        </div>
                    @endforeach
                </div>
                <p class="text-gray-600">Join the puzzle-solving community!</p>
            </div>

            <!-- Step 1: Initial Registration Form -->
            <form method="POST" action="{{ route('register.sendOtp') }}" class="space-y-6 relative z-10" id="registrationForm">
                @csrf

                <!-- Name -->
                <div class="bg-white/90 rounded-lg p-4 shadow-sm">
                    <x-input-label for="name" :value="__('Name')" class="text-gray-700" />
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <x-text-input id="name" class="block mt-1 w-full pl-10 bg-white" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Your full name" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email -->
                <div class="bg-white/90 rounded-lg p-4 shadow-sm">
                    <x-input-label for="email" :value="__('Email')" class="text-gray-700" />
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <x-text-input id="email" class="block mt-1 w-full pl-10 bg-white" type="email" name="email" :value="old('email')" required autocomplete="email" placeholder="your@email.com" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Phone -->
                <div class="bg-white/90 rounded-lg p-4 shadow-sm">
                    <x-input-label for="phone" :value="__('Phone')" class="text-gray-700" />
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <x-text-input id="phone" class="block mt-1 w-full pl-10 bg-white" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="+1 (555) 000-0000" />
                    </div>
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <!-- Company Name -->
                <div class="bg-white/90 rounded-lg p-4 shadow-sm">
                    <x-input-label for="company_name" :value="__('Company Name (Optional)')" class="text-gray-700" />
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <x-text-input id="company_name" class="block mt-1 w-full pl-10 bg-white" type="text" name="company_name" :value="old('company_name')" autocomplete="organization" placeholder="Your company name" />
                    </div>
                    <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                </div>

                <div class="flex flex-col space-y-4 bg-white/90 rounded-lg p-4 shadow-sm">
                    <x-primary-button class="w-full justify-center bg-blue-600 hover:bg-blue-700">
                        {{ __('Send Verification Code') }}
                    </x-primary-button>

                    <div class="text-center">
                        <span class="text-gray-600">Already have an account?</span>
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold ml-1">
                            Log in
                        </a>
                    </div>
                </div>
            </form>

            <!-- Step 2: OTP Verification Form (Hidden by default) -->
            <form method="POST" action="{{ route('register.verifyOtp') }}" class="space-y-6 relative z-10 hidden" id="otpVerificationForm">
                @csrf
                <div class="bg-white/90 rounded-lg p-4 shadow-sm">
                    <x-input-label for="otp" :value="__('Enter Verification Code')" class="text-gray-700" />
                    <p class="text-sm text-gray-600 mb-4">We've sent a verification code to your email. Please enter it below.</p>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <x-text-input id="otp" class="block mt-1 w-full pl-10 bg-white text-center tracking-[1em] text-xl" type="text" name="otp" required maxlength="6" placeholder="000000" />
                    </div>
                    <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                    
                    <div class="mt-4 text-center">
                        <button type="button" class="text-sm text-blue-600 hover:text-blue-800" id="resendOtp">
                            Didn't receive the code? Resend
                        </button>
                        <div class="text-sm text-gray-500 hidden" id="resendTimer">
                            Resend code in <span id="countdown">60</span>s
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <x-primary-button type="submit" id="verifyOtpBtn" onclick="verifyOtp(event)">
                        {{ __('Verify OTP') }}
                    </x-primary-button>
                </div>
            </form>

            <script>
                async function verifyOtp(e) {
                    e.preventDefault();
                    const form = document.getElementById('otpVerificationForm');
                    const formData = new FormData(form);
                    
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(Object.fromEntries(formData))
                        });

                        const result = await response.json();
                        
                        if (result.success) {
                            window.location.href = result.redirect;
                        } else {
                            alert(result.message || 'Verification failed. Please try again.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    }
                }
            </script>
        </div>
    </div>

    <style>
    .aspect-square {
        aspect-ratio: 1 / 1;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const registrationForm = document.getElementById('registrationForm');
        const otpVerificationForm = document.getElementById('otpVerificationForm');
        const resendOtpButton = document.getElementById('resendOtp');
        const resendTimer = document.getElementById('resendTimer');
        const countdownSpan = document.getElementById('countdown');
        let countdownInterval;

        // Handle registration form submission
        registrationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    registrationForm.classList.add('hidden');
                    otpVerificationForm.classList.remove('hidden');
                    startResendTimer();
                } else {
                    alert(data.message || 'Something went wrong. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });

        // Handle OTP resend
        resendOtpButton.addEventListener('click', async function() {
            try {
                const response = await fetch('{{ route("register.resendOtp") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    startResendTimer();
                } else {
                    alert(data.message || 'Failed to resend code. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });

        function startResendTimer() {
            let timeLeft = 60;
            resendOtpButton.classList.add('hidden');
            resendTimer.classList.remove('hidden');
            
            if (countdownInterval) clearInterval(countdownInterval);
            
            countdownInterval = setInterval(() => {
                timeLeft--;
                countdownSpan.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    resendOtpButton.classList.remove('hidden');
                    resendTimer.classList.add('hidden');
                }
            }, 1000);
        }
    });
    </script>
</body>
</html>
