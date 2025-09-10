@extends('layouts.app')

@section('title', 'Account Pending Approval - Budget Tracker')

@section('content')
<div style="max-width: 600px; margin: 0 auto; text-center;">
    <div class="card">
        <div style="font-size: 4rem; margin-bottom: 2rem;">⏳</div>
        
        <h1 style="font-size: 2.5rem; font-weight: 600; margin-bottom: 1rem; color: #f59e0b;">
            Account Pending Approval
        </h1>
        
        <p style="font-size: 1.125rem; color: #6b7280; margin-bottom: 2rem;">
            Thank you for registering! Your account is currently pending admin approval. 
            You will be notified once your account has been reviewed.
        </p>

        <div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #92400e;">
                What happens next?
            </h3>
            <ul style="text-align: left; color: #92400e; line-height: 1.8;">
                <li>An admin will review your registration details</li>
                <li>You'll receive an email notification once approved</li>
                <li>You can then log in and start using the app</li>
                <li>This process usually takes 24-48 hours</li>
            </ul>
        </div>

        <div style="background: #f3f4f6; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
                Need Help?
            </h3>
            <p style="color: #6b7280; margin-bottom: 1rem;">
                If you have any questions or need assistance, please contact our support team.
            </p>
            <a href="mailto:support@budgettracker.com" style="color: #3b82f6; text-decoration: none;">
                support@budgettracker.com
            </a>
        </div>

        <div style="margin-top: 2rem;">
            <a href="{{ route('login') }}" class="btn btn-primary" style="margin-right: 1rem;">
                Check Status
            </a>
            <a href="{{ route('landing') }}" class="btn btn-secondary">
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
