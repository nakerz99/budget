@extends('layouts.app')

@section('title', 'Login - Budget Tracker')

@section('content')
<div style="max-width: 400px; margin: 0 auto;">
    <div class="card">
        <div class="text-center mb-4">
            <h1 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">Welcome Back</h1>
            <p style="color: #6b7280;">Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input @error('username') border-red-500 @enderror" 
                    value="{{ old('username') }}" 
                    required 
                    autofocus
                >
                @error('username')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="pin" class="form-label">6-Digit PIN</label>
                <input 
                    type="password" 
                    id="pin" 
                    name="pin" 
                    class="form-input @error('pin') border-red-500 @enderror" 
                    maxlength="6" 
                    pattern="[0-9]{6}" 
                    required
                    placeholder="123456"
                >
                @error('pin')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Sign In
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p style="color: #6b7280;">
                Don't have an account? 
                <a href="{{ route('signup') }}" style="color: #3b82f6; text-decoration: none;">Sign up here</a>
            </p>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('landing') }}" style="color: #6b7280; text-decoration: none;">
            ← Back to Home
        </a>
    </div>
</div>

@push('scripts')
<script>
    // Auto-format PIN input to only allow numbers
    document.getElementById('pin').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
@endpush
@endsection
