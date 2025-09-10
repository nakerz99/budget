@extends('layouts.app')

@section('title', 'Account Rejected - Budget Tracker')

@section('content')
<div style="max-width: 600px; margin: 0 auto; text-center;">
    <div class="card">
        <div style="font-size: 4rem; margin-bottom: 2rem;">❌</div>
        
        <h1 style="font-size: 2.5rem; font-weight: 600; margin-bottom: 1rem; color: #dc2626;">
            Account Rejected
        </h1>
        
        <p style="font-size: 1.125rem; color: #6b7280; margin-bottom: 2rem;">
            Unfortunately, your account registration has been rejected. 
            Please review the information below and consider reapplying.
        </p>

        <div style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #991b1b;">
                Reason for Rejection
            </h3>
            <p style="color: #991b1b; text-align: left;">
                Your account was rejected due to incomplete or invalid information provided during registration. 
                Please ensure all required fields are filled correctly and try again.
            </p>
        </div>

        <div style="background: #f3f4f6; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
                What can you do?
            </h3>
            <ul style="text-align: left; color: #6b7280; line-height: 1.8;">
                <li>Review your registration information</li>
                <li>Ensure all fields are filled correctly</li>
                <li>Use a valid username and PIN</li>
                <li>Try registering again with corrected information</li>
            </ul>
        </div>

        <div style="background: #f3f4f6; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
                Need Help?
            </h3>
            <p style="color: #6b7280; margin-bottom: 1rem;">
                If you believe this is an error or need assistance, please contact our support team.
            </p>
            <a href="mailto:support@budgettracker.com" style="color: #3b82f6; text-decoration: none;">
                support@budgettracker.com
            </a>
        </div>

        <div style="margin-top: 2rem;">
            <a href="{{ route('signup') }}" class="btn btn-primary" style="margin-right: 1rem;">
                Try Again
            </a>
            <a href="{{ route('landing') }}" class="btn btn-secondary">
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
