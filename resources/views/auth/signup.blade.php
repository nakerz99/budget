<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NR Budget Tracker') }} - Sign Up</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'pulse-slow': 'pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
</head>
<body>

@section('content')
<div class="min-h-screen relative overflow-hidden">
    <!-- Background with animated gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-900 via-teal-900 to-cyan-900">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <!-- Animated background elements -->
        <div class="absolute top-0 left-0 w-full h-full">
            <div class="absolute top-20 left-20 w-72 h-72 bg-emerald-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
            <div class="absolute top-40 right-20 w-72 h-72 bg-cyan-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-1/2 w-72 h-72 bg-teal-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse animation-delay-4000"></div>
        </div>
    </div>

    <!-- Main content -->
    <div class="relative z-10 flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg w-full">
            <!-- Logo and header -->
            <div class="text-center mb-8">
                <div class="mx-auto h-20 w-20 bg-white bg-opacity-20 backdrop-blur-lg rounded-2xl flex items-center justify-center mb-6 shadow-2xl">
                    <i class="fas fa-user-plus text-white text-3xl"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Join Us Today</h1>
                <p class="text-cyan-200 text-lg">Start your financial journey with Budget Tracker</p>
            </div>

            <!-- Registration card -->
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-3xl shadow-2xl border border-white border-opacity-20 p-8">
                <form method="POST" action="{{ route('signup') }}" class="space-y-6">
                    @csrf

                    <!-- Full Name Field -->
                    <div class="space-y-2">
                        <label for="full_name" class="block text-sm font-semibold text-white">
                            Full Name
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-cyan-300 group-focus-within:text-white transition-colors"></i>
                            </div>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                class="w-full pl-12 pr-4 py-4 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-2xl text-white placeholder-cyan-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-300 backdrop-blur-sm @error('full_name') border-red-400 focus:ring-red-400 @enderror" 
                                value="{{ old('full_name') }}" 
                                required 
                                autofocus
                                placeholder="Enter your full name"
                            >
                        </div>
                        @error('full_name')
                            <p class="text-red-300 text-sm flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Username Field -->
                    <div class="space-y-2">
                        <label for="username" class="block text-sm font-semibold text-white">
                            Username
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-at text-cyan-300 group-focus-within:text-white transition-colors"></i>
                            </div>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="w-full pl-12 pr-4 py-4 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-2xl text-white placeholder-cyan-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-300 backdrop-blur-sm @error('username') border-red-400 focus:ring-red-400 @enderror" 
                                value="{{ old('username') }}" 
                                required
                                minlength="3"
                                maxlength="50"
                                placeholder="Choose a username"
                            >
                        </div>
                        @error('username')
                            <p class="text-red-300 text-sm flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- PIN Field -->
                    <div class="space-y-2">
                        <label for="pin" class="block text-sm font-semibold text-white">
                            6-Digit PIN
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-cyan-300 group-focus-within:text-white transition-colors"></i>
                            </div>
                            <input 
                                type="password" 
                                id="pin" 
                                name="pin" 
                                class="w-full pl-12 pr-4 py-4 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-2xl text-white placeholder-cyan-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-300 backdrop-blur-sm @error('pin') border-red-400 focus:ring-red-400 @enderror" 
                                maxlength="6" 
                                pattern="[0-9]{6}" 
                                required
                                placeholder="••••••"
                            >
                        </div>
                        @error('pin')
                            <p class="text-red-300 text-sm flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Confirm PIN Field -->
                    <div class="space-y-2">
                        <label for="pin_confirmation" class="block text-sm font-semibold text-white">
                            Confirm PIN
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-key text-cyan-300 group-focus-within:text-white transition-colors"></i>
                            </div>
                            <input 
                                type="password" 
                                id="pin_confirmation" 
                                name="pin_confirmation" 
                                class="w-full pl-12 pr-4 py-4 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-2xl text-white placeholder-cyan-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-300 backdrop-blur-sm @error('pin_confirmation') border-red-400 focus:ring-red-400 @enderror" 
                                maxlength="6" 
                                pattern="[0-9]{6}" 
                                required
                                placeholder="••••••"
                            >
                        </div>
                        @error('pin_confirmation')
                            <p class="text-red-300 text-sm flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Terms and Privacy Checkboxes -->
                    <div class="space-y-4 pt-2">
                        <div class="flex items-start space-x-3">
                            <div class="flex items-center h-6">
                                <input 
                                    type="checkbox" 
                                    name="terms" 
                                    required
                                    class="w-5 h-5 text-emerald-600 bg-white bg-opacity-20 border-white border-opacity-30 rounded focus:ring-emerald-500 focus:ring-2"
                                >
                            </div>
                            <div class="text-sm">
                                <label class="text-cyan-200">
                                    I agree to the <a href="#" class="text-white font-semibold hover:text-cyan-200 transition-colors underline decoration-1 underline-offset-2">Terms of Service</a> and <a href="#" class="text-white font-semibold hover:text-cyan-200 transition-colors underline decoration-1 underline-offset-2">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                        @error('terms')
                            <p class="text-red-300 text-sm flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror

                        <div class="flex items-start space-x-3">
                            <div class="flex items-center h-6">
                                <input 
                                    type="checkbox" 
                                    name="privacy" 
                                    required
                                    class="w-5 h-5 text-emerald-600 bg-white bg-opacity-20 border-white border-opacity-30 rounded focus:ring-emerald-500 focus:ring-2"
                                >
                            </div>
                            <div class="text-sm">
                                <label class="text-cyan-200">
                                    I consent to the processing of my personal data for account creation and service provision
                                </label>
                            </div>
                        </div>
                        @error('privacy')
                            <p class="text-red-300 text-sm flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" class="w-full py-4 px-6 bg-gradient-to-r from-emerald-600 to-cyan-600 hover:from-emerald-700 hover:to-cyan-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-emerald-300 focus:ring-opacity-50">
                            <i class="fas fa-user-plus mr-2"></i>
                            Create Account
                        </button>
                    </div>
                </form>

                <!-- Links -->
                <div class="mt-8 text-center space-y-4">
                    <p class="text-cyan-200">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-white font-semibold hover:text-cyan-200 transition-colors duration-300 underline decoration-2 underline-offset-4">
                            Sign in here
                        </a>
                    </p>
                    
                    <div class="pt-4">
                        <a href="{{ route('landing') }}" class="inline-flex items-center text-cyan-200 hover:text-white transition-colors duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-cyan-200 text-sm">
                    Join thousands of users managing their finances
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-format PIN inputs to only allow numbers
    document.getElementById('pin').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('pin_confirmation').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
        // Add floating animation to background elements
        const floatingElements = document.querySelectorAll('.animate-pulse');
        floatingElements.forEach((element, index) => {
            element.style.animationDelay = `${index * 2}s`;
            element.style.animationDuration = '6s';
        });

        // Add form validation feedback
        const inputs = document.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('border-red-400');
                } else {
                    this.classList.remove('border-red-400');
                }
            });
        });
    });
</script>

<style>
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    .animation-delay-4000 {
        animation-delay: 4s;
    }
    
    /* Custom scrollbar for webkit browsers */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    /* Custom checkbox styling */
    input[type="checkbox"] {
        appearance: none;
        background-color: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 4px;
        width: 20px;
        height: 20px;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    input[type="checkbox"]:checked {
        background-color: #10b981;
        border-color: #10b981;
    }

    input[type="checkbox"]:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
        font-weight: bold;
    }
</style>
@endpush

</body>
</html>