@extends('layouts.app')

@section('title', 'Account Approved - Budget Tracker')

@section('content')
<div style="max-width: 600px; margin: 0 auto; text-center;">
    <div class="card">
        <div style="font-size: 4rem; margin-bottom: 2rem;">✅</div>
        
        <h1 style="font-size: 2.5rem; font-weight: 600; margin-bottom: 1rem; color: #059669;">
            Account Approved!
        </h1>
        
        <p style="font-size: 1.125rem; color: #6b7280; margin-bottom: 2rem;">
            Congratulations! Your account has been approved and you can now access all features of Budget Tracker.
        </p>

        <div style="background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #065f46;">
                Welcome to Budget Tracker!
            </h3>
            <ul style="text-align: left; color: #065f46; line-height: 1.8;">
                <li>Start tracking your expenses and income</li>
                <li>Set up your monthly budgets</li>
                <li>Create savings goals</li>
                <li>Manage your bills and subscriptions</li>
                <li>Generate detailed financial reports</li>
            </ul>
        </div>

        <div style="background: #f3f4f6; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
                Getting Started
            </h3>
            <p style="color: #6b7280; margin-bottom: 1rem;">
                We recommend starting by setting up your first budget and adding some initial transactions 
                to get familiar with the app.
            </p>
        </div>

        <div style="margin-top: 2rem;">
            <a href="{{ route('dashboard') }}" class="btn btn-primary" style="margin-right: 1rem;">
                Go to Dashboard
            </a>
            <a href="{{ route('landing') }}" class="btn btn-secondary">
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
