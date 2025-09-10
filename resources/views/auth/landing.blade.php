@extends('layouts.app')

@section('title', 'Budget Tracker - Take Control of Your Finances')

@section('content')
<div class="text-center">
    <h1 style="font-size: 3rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">
        Take Control of Your Finances
    </h1>
    <p style="font-size: 1.25rem; color: #6b7280; margin-bottom: 3rem; max-width: 600px; margin-left: auto; margin-right: auto;">
        Track expenses, manage budgets, and achieve your financial goals with our comprehensive budget tracking app.
    </p>

    <div style="margin-bottom: 4rem;">
        <a href="{{ route('signup') }}" class="btn btn-primary" style="margin-right: 1rem; font-size: 1.125rem; padding: 1rem 2rem;">
            Get Started Free
        </a>
        <a href="{{ route('login') }}" class="btn btn-secondary" style="font-size: 1.125rem; padding: 1rem 2rem;">
            Login
        </a>
    </div>

    <div class="grid grid-3" style="margin-top: 4rem;">
        <div class="card text-center">
            <div style="font-size: 3rem; margin-bottom: 1rem;">💰</div>
            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Budget Planning</h3>
            <p style="color: #6b7280;">Set monthly budgets for different categories and track your spending against them.</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Expense Tracking</h3>
            <p style="color: #6b7280;">Track daily, weekly, and monthly expenses with detailed categorization.</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎯</div>
            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Savings Goals</h3>
            <p style="color: #6b7280;">Set and track your savings goals with visual progress indicators.</p>
        </div>
    </div>

    <div class="grid grid-3" style="margin-top: 2rem;">
        <div class="card text-center">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Bill Management</h3>
            <p style="color: #6b7280;">Never miss a bill with automated reminders and payment tracking.</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📈</div>
            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Analytics</h3>
            <p style="color: #6b7280;">Get insights into your spending patterns with detailed reports and charts.</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔒</div>
            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Secure & Private</h3>
            <p style="color: #6b7280;">Your financial data is encrypted and secure with admin approval system.</p>
        </div>
    </div>

    <div style="margin-top: 4rem; padding: 2rem; background: #f3f4f6; border-radius: 8px;">
        <h2 style="font-size: 2rem; font-weight: 600; margin-bottom: 1rem;">Ready to Get Started?</h2>
        <p style="color: #6b7280; margin-bottom: 2rem;">Join thousands of users who are already taking control of their finances.</p>
        <a href="{{ route('signup') }}" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">
            Create Your Account
        </a>
    </div>
</div>
@endsection
