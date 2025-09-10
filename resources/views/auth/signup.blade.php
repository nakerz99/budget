@extends('layouts.app')

@section('title', 'Sign Up - Budget Tracker')

@section('content')
<div style="max-width: 500px; margin: 0 auto;">
    <div class="card">
        <div class="text-center mb-4">
            <h1 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">Create Account</h1>
            <p style="color: #6b7280;">Join Budget Tracker and take control of your finances</p>
        </div>

        <form method="POST" action="{{ route('signup') }}">
            @csrf

            <div class="form-group">
                <label for="full_name" class="form-label">Full Name</label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    class="form-input @error('full_name') border-red-500 @enderror" 
                    value="{{ old('full_name') }}" 
                    required 
                    autofocus
                >
                @error('full_name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input @error('username') border-red-500 @enderror" 
                    value="{{ old('username') }}" 
                    required
                    minlength="3"
                    maxlength="50"
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
                <label for="pin_confirmation" class="form-label">Confirm PIN</label>
                <input 
                    type="password" 
                    id="pin_confirmation" 
                    name="pin_confirmation" 
                    class="form-input @error('pin_confirmation') border-red-500 @enderror" 
                    maxlength="6" 
                    pattern="[0-9]{6}" 
                    required
                    placeholder="123456"
                >
                @error('pin_confirmation')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>


            <div class="form-group">
                <label style="display: flex; align-items: flex-start; gap: 0.5rem;">
                    <input 
                        type="checkbox" 
                        name="terms" 
                        required
                        style="margin-top: 0.25rem;"
                    >
                    <span style="font-size: 0.875rem; color: #6b7280;">
                        I agree to the <a href="#" style="color: #3b82f6;">Terms of Service</a> and <a href="#" style="color: #3b82f6;">Privacy Policy</a>
                    </span>
                </label>
                @error('terms')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: flex-start; gap: 0.5rem;">
                    <input 
                        type="checkbox" 
                        name="privacy" 
                        required
                        style="margin-top: 0.25rem;"
                    >
                    <span style="font-size: 0.875rem; color: #6b7280;">
                        I consent to the processing of my personal data for account creation and service provision
                    </span>
                </label>
                @error('privacy')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Create Account
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p style="color: #6b7280;">
                Already have an account? 
                <a href="{{ route('login') }}" style="color: #3b82f6; text-decoration: none;">Sign in here</a>
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
    // Auto-format PIN inputs to only allow numbers
    document.getElementById('pin').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('pin_confirmation').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Focus on PIN confirmation when PIN is complete
    document.getElementById('pin').addEventListener('input', function(e) {
        if (this.value.length === 6) {
            document.getElementById('pin_confirmation').focus();
        }
    });
</script>
@endpush
@endsection
