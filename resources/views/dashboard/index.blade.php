@extends('layouts.app')

@section('title', 'Dashboard - Budget Tracker')

@section('content')
<div>
    <h1 style="font-size: 2.5rem; font-weight: 600; margin-bottom: 2rem;">
        Welcome back, {{ $user->full_name }}!
    </h1>

    <div class="grid grid-4" style="margin-bottom: 3rem;">
        <div class="card text-center">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #dc2626;">💰</div>
            <h3 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">{{ currency_symbol() }}{{ number_format(abs($monthlySpending), 2) }}</h3>
            <p style="color: #6b7280;">Monthly Spending</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #3b82f6;">📊</div>
            <h3 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">{{ currency_symbol() }}{{ number_format($monthlyBudget, 2) }}</h3>
            <p style="color: #6b7280;">Monthly Budget</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #059669;">💳</div>
            <h3 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">{{ currency_symbol() }}{{ number_format($accountBalance, 2) }}</h3>
            <p style="color: #6b7280;">Account Balance</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #8b5cf6;">🎯</div>
            <h3 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $savingsGoalsCount }}</h3>
            <p style="color: #6b7280;">Active Savings Goals</p>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Recent Transactions</h2>
            @if($recentTransactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_date->format('M d') }}</td>
                                <td>{{ Str::limit($transaction->description ?: 'No description', 20) }}</td>
                                <td>
                                    <span class="badge" style="background-color: {{ $transaction->category->color }};">
                                        {{ $transaction->category->name }}
                                    </span>
                                </td>
                                <td class="text-end {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ currency_symbol() }}{{ number_format(abs($transaction->amount), 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-center mt-2">
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-primary">View All Transactions</a>
                    </div>
                </div>
            @else
                <div style="text-align: center; padding: 2rem; color: #6b7280;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                    <p>No transactions yet. Start by adding your first transaction!</p>
                </div>
            @endif
        </div>

        <div class="card">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Quick Actions</h2>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <a href="{{ route('transactions.index') }}" class="btn btn-primary" style="text-align: center;">
                    View Transactions
                </a>
                <a href="{{ route('budget.index') }}" class="btn btn-secondary" style="text-align: center;">
                    Set Budget
                </a>
                <a href="{{ route('savings.index') }}" class="btn btn-secondary" style="text-align: center;">
                    Create Savings Goal
                </a>
                <a href="{{ route('bills.index') }}" class="btn btn-secondary" style="text-align: center;">
                    Manage Bills
                </a>
            </div>
        </div>
    </div>

    @if($upcomingBills->count() > 0)
    <div class="card" style="margin-top: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Upcoming Bills</h2>
        <div class="table-responsive">
            <table class="table table-sm">
                <tbody>
                    @foreach($upcomingBills as $bill)
                    <tr>
                        <td>{{ $bill->due_date->format('M d') }}</td>
                        <td>{{ $bill->name }}</td>
                        <td class="text-end">
                            <span class="badge bg-warning">{{ currency_symbol() }}{{ number_format($bill->amount, 2) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-center mt-2">
                <a href="{{ route('bills.index') }}" class="btn btn-sm btn-warning">View All Bills</a>
            </div>
        </div>
    </div>
    @endif

    <div class="card" style="margin-top: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Getting Started</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div style="padding: 1.5rem; background: #f8fafc; border-radius: 8px;">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">1. Add Your First Account</h3>
                <p style="color: #6b7280; margin-bottom: 1rem;">Create accounts for your checking, savings, and credit cards.</p>
                <button class="btn btn-primary" style="width: 100%;">Add Account</button>
            </div>
            
            <div style="padding: 1.5rem; background: #f8fafc; border-radius: 8px;">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">2. Set Up Categories</h3>
                <p style="color: #6b7280; margin-bottom: 1rem;">Create categories for your expenses and income.</p>
                <button class="btn btn-primary" style="width: 100%;">Manage Categories</button>
            </div>
            
            <div style="padding: 1.5rem; background: #f8fafc; border-radius: 8px;">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">3. Create Your First Budget</h3>
                <p style="color: #6b7280; margin-bottom: 1rem;">Set monthly spending limits for different categories.</p>
                <button class="btn btn-primary" style="width: 100%;">Create Budget</button>
            </div>
        </div>
    </div>
</div>

<style>
.grid-4 {
    grid-template-columns: repeat(4, 1fr);
}

@media (max-width: 768px) {
    .grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .grid-4 {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
