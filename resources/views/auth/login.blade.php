<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NR Budget Tracker') }} - Login</title>

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
    <div class="absolute inset-0 bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <!-- Animated background elements -->
        <div class="absolute top-0 left-0 w-full h-full">
            <div class="absolute top-20 left-20 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
            <div class="absolute top-40 right-20 w-72 h-72 bg-yellow-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-1/2 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse animation-delay-4000"></div>
        </div>
    </div>

    <!-- Main content -->
    <div class="relative z-10 flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo and header -->
            <div class="text-center mb-8">
                <div class="mx-auto h-20 w-20 bg-white bg-opacity-20 backdrop-blur-lg rounded-2xl flex items-center justify-center mb-6 shadow-2xl">
                    <i class="fas fa-wallet text-white text-3xl"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Welcome Back</h1>
                <p class="text-blue-200 text-lg">Sign in to continue your financial journey</p>
            </div>

            <!-- Login card -->
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-3xl shadow-2xl border border-white border-opacity-20 p-8">
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Username Field -->
                    <div class="space-y-2">
                        <label for="username" class="block text-sm font-semibold text-white">
                            Username
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user text-blue-300 group-focus-within:text-white transition-colors"></i>
                            </div>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="w-full pl-12 pr-4 py-4 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-2xl text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-300 backdrop-blur-sm @error('username') border-red-400 focus:ring-red-400 @enderror" 
                                value="{{ old('username') }}" 
                                required 
                                autofocus
                                placeholder="Enter your username"
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
                                <i class="fas fa-lock text-blue-300 group-focus-within:text-white transition-colors"></i>
                            </div>
                            <input 
                                type="password" 
                                id="pin" 
                                name="pin" 
                                class="w-full pl-12 pr-4 py-4 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-2xl text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-300 backdrop-blur-sm @error('pin') border-red-400 focus:ring-red-400 @enderror" 
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

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" class="w-full py-4 px-6 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-purple-300 focus:ring-opacity-50">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In
                        </button>
                    </div>
                </form>

                <!-- Links -->
                <div class="mt-8 text-center space-y-4">
                    <p class="text-blue-200">
                        Don't have an account? 
                        <a href="{{ route('signup') }}" class="text-white font-semibold hover:text-blue-200 transition-colors duration-300 underline decoration-2 underline-offset-4">
                            Create one here
                        </a>
                    </p>
                    
                    <div class="pt-4">
                        <a href="{{ route('landing') }}" class="inline-flex items-center text-blue-200 hover:text-white transition-colors duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-blue-200 text-sm">
                    Secure • Fast • Reliable
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-format PIN input to only allow numbers
    document.getElementById('pin').addEventListener('input', function(e) {
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
</style>
@endpush

</body>
</html>
