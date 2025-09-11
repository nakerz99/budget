@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div>
    <!-- Header with integrated summary -->
    <div class="mb-4 lg:mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-3 lg:mb-4">
            <!-- Left side: Welcome message -->
            <div class="flex-1">
                <h1 class="text-xl lg:text-2xl font-bold text-gray-800 mb-1">
                    Welcome back, {{ $user->full_name }}! 👋
                </h1>
                <p class="text-sm lg:text-base text-gray-600">Here's your financial overview for {{ now()->format('F Y') }}</p>
            </div>
            
            <!-- Right side: Financial Summary -->
            <div class="financial-summary-inline">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-3">
                    <div class="stat-card-inline expense">
                        <div class="stat-icon-inline">💸</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-red-600">{{ currency_symbol() }}{{ number_format(abs($monthlySpending), 0) }}</div>
                            <div class="stat-label-inline">Spending</div>
                        </div>
                    </div>

                    <div class="stat-card-inline balance">
                        <div class="stat-icon-inline">💰</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-blue-600">{{ currency_symbol() }}{{ number_format($accountBalance, 0) }}</div>
                            <div class="stat-label-inline">Balance</div>
                        </div>
                    </div>

                    <div class="stat-card-inline income">
                        <div class="stat-icon-inline">📈</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-green-600">{{ currency_symbol() }}{{ number_format($monthlyBudget, 0) }}</div>
                            <div class="stat-label-inline">Budget</div>
                        </div>
                    </div>

                    <div class="stat-card-inline savings">
                        <div class="stat-icon-inline">🎯</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-purple-600">{{ $savingsGoalsCount }}</div>
                            <div class="stat-label-inline">Goals</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Recent Transactions -->
    <div class="card mb-4 lg:mb-6">
        <div class="card-header">
            <i class="fas fa-history"></i> Recent Transactions
        </div>
        <div class="card-body p-3 lg:p-4">
            @if($recentTransactions->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="table-container">
                    <table class="table">
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: {{ $transaction->category->color }}20; color: {{ $transaction->category->color }};">
                                            <i class="fas fa-{{ $transaction->type === 'income' ? 'arrow-up' : 'arrow-down' }} text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800 text-sm">{{ Str::limit($transaction->description ?: 'No description', 25) }}</div>
                                            <div class="text-xs text-gray-500">{{ $transaction->category->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="font-semibold text-sm {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->type === 'income' ? '+' : '-' }}{{ currency_symbol() }}{{ number_format($transaction->amount, 0) }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $transaction->transaction_date->format('M d') }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden space-y-2">
                @foreach($recentTransactions as $transaction)
                <div class="transaction-item-compact bg-white border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" 
                             style="background: {{ $transaction->category->color }}20; color: {{ $transaction->category->color }};">
                            <i class="fas fa-{{ $transaction->type === 'income' ? 'arrow-up' : 'arrow-down' }} text-xs"></i>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-800 text-sm truncate">
                                        {{ $transaction->description ?: 'No description' }}
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-gray-500">{{ $transaction->category->name }}</span>
                                        <span class="text-xs text-gray-400">•</span>
                                        <span class="text-xs text-gray-400">{{ $transaction->transaction_date->format('M d') }}</span>
                                    </div>
                                </div>
                                
                                <div class="font-semibold text-sm {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }} ml-2">
                                    {{ $transaction->type === 'income' ? '+' : '-' }}{{ currency_symbol() }}{{ number_format($transaction->amount, 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="text-center mt-3 lg:mt-4">
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-primary">
                    View All Transactions
                </a>
            </div>
            @else
            <div class="text-center py-6 lg:py-8">
                <div class="text-3xl lg:text-4xl mb-3 lg:mb-4">📝</div>
                <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-2">No transactions yet</h3>
                <p class="text-sm lg:text-base text-gray-600 mb-4">Start by adding your first transaction!</p>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Transaction
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Bills -->
    @if($upcomingBills->count() > 0)
    <div class="card mb-4 lg:mb-6">
        <div class="card-header">
            <i class="fas fa-calendar-alt"></i> Upcoming Bills
        </div>
        <div class="card-body p-3 lg:p-4">
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="table-container">
                    <table class="table">
                        <tbody>
                            @foreach($upcomingBills as $bill)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center">
                                            <i class="fas fa-file-invoice text-orange-600 text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800 text-sm">{{ $bill->name }}</div>
                                            <div class="text-xs text-gray-500">Due {{ $bill->due_date->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="font-semibold text-sm text-gray-800">{{ currency_symbol() }}{{ number_format($bill->amount, 0) }}</div>
                                    <div class="text-xs text-gray-500">
                                        @if($bill->due_date->isToday())
                                        <span class="text-red-600">Due Today</span>
                                        @elseif($bill->due_date->isTomorrow())
                                        <span class="text-orange-600">Due Tomorrow</span>
                                        @else
                                        {{ $bill->due_date->diffForHumans() }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden space-y-2">
                @foreach($upcomingBills as $bill)
                <div class="bill-item-compact bg-white border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file-invoice text-orange-600 text-xs"></i>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-800 text-sm truncate">{{ $bill->name }}</div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-gray-500">Due {{ $bill->due_date->format('M d') }}</span>
                                        @if($bill->due_date->isToday())
                                        <span class="text-xs text-red-600 font-medium">• Due Today</span>
                                        @elseif($bill->due_date->isTomorrow())
                                        <span class="text-xs text-orange-600 font-medium">• Due Tomorrow</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="font-semibold text-sm text-gray-800 ml-2">
                                    {{ currency_symbol() }}{{ number_format($bill->amount, 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="text-center mt-3 lg:mt-4">
                <a href="{{ route('bills.index') }}" class="btn btn-sm btn-warning">
                    View All Bills
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Savings Goals -->
    @if($savingsGoals->count() > 0)
    <div class="card mb-4 lg:mb-6">
        <div class="card-header">
            <i class="fas fa-piggy-bank"></i> Savings Goals
        </div>
        <div class="card-body p-3 lg:p-4">
            @foreach($savingsGoals as $goal)
            <div class="savings-goal-item-compact mb-3 last:mb-0">
                <div class="flex justify-between items-center mb-2">
                    <div class="font-medium text-gray-800 text-sm">{{ $goal->name }}</div>
                    <div class="text-xs text-gray-600">{{ currency_symbol() }}{{ number_format($goal->current_amount, 0) }} / {{ currency_symbol() }}{{ number_format($goal->target_amount, 0) }}</div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(100, ($goal->current_amount / $goal->target_amount) * 100) }}%"></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ number_format(($goal->current_amount / $goal->target_amount) * 100, 1) }}% complete
                </div>
            </div>
            @endforeach
            <div class="text-center mt-3 lg:mt-4">
                <a href="{{ route('savings.index') }}" class="btn btn-sm btn-primary">
                    Manage Goals
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Getting Started (for new users) -->
    @if($recentTransactions->count() == 0)
    <div class="card">
        <div class="card-header">
            <i class="fas fa-rocket"></i> Getting Started
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">1</div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Add Your First Account</h3>
                            <p class="text-sm text-gray-600 mb-3">Create accounts for your checking, savings, and credit cards.</p>
                            <a href="{{ route('settings.index') }}" class="btn btn-sm btn-primary">Add Account</a>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white font-bold">2</div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Set Up Categories</h3>
                            <p class="text-sm text-gray-600 mb-3">Create categories for your expenses and income.</p>
                            <a href="{{ route('settings.index') }}" class="btn btn-sm btn-success">Manage Categories</a>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-purple-50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold">3</div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Create Your First Budget</h3>
                            <p class="text-sm text-gray-600 mb-3">Set monthly spending limits for different categories.</p>
                            <a href="{{ route('budget.index') }}" class="btn btn-sm btn-warning">Set Budget</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .w-10 {
        width: 2.5rem;
    }

    .h-10 {
        height: 2.5rem;
    }

    .w-8 {
        width: 2rem;
    }

    .h-8 {
        height: 2rem;
    }

    .h-2 {
        height: 0.5rem;
    }

    .gap-3 {
        gap: 0.75rem;
    }

    .gap-4 {
        gap: 1rem;
    }

    .items-center {
        align-items: center;
    }

    .items-start {
        align-items: flex-start;
    }

    .justify-center {
        justify-content: center;
    }

    .justify-between {
        justify-content: space-between;
    }

    .rounded-full {
        border-radius: 9999px;
    }

    .rounded-lg {
        border-radius: 0.5rem;
    }

    .text-green-600 {
        color: #059669;
    }

    .text-red-600 {
        color: #dc2626;
    }

    .text-orange-600 {
        color: #ea580c;
    }

    .text-gray-500 {
        color: #6b7280;
    }

    .text-gray-600 {
        color: #4b5563;
    }

    .text-gray-800 {
        color: #1f2937;
    }

    .bg-orange-100 {
        background-color: #fed7aa;
    }

    .bg-blue-50 {
        background-color: #eff6ff;
    }

    .bg-green-50 {
        background-color: #f0fdf4;
    }

    .bg-purple-50 {
        background-color: #faf5ff;
    }

    .bg-blue-600 {
        background-color: #2563eb;
    }

    .bg-green-600 {
        background-color: #059669;
    }

    .bg-purple-600 {
        background-color: #9333ea;
    }

    .text-orange-600 {
        color: #ea580c;
    }

    .text-white {
        color: white;
    }

    .font-bold {
        font-weight: 700;
    }

    .font-semibold {
        font-weight: 600;
    }

    .font-medium {
        font-weight: 500;
    }

    .last\:mb-0:last-child {
        margin-bottom: 0;
    }

    .grid-cols-1 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    .p-4 {
        padding: 1rem;
    }

    .mb-1 {
        margin-bottom: 0.25rem;
    }

    .mb-2 {
        margin-bottom: 0.5rem;
    }

    .mb-3 {
        margin-bottom: 0.75rem;
    }

    .mb-4 {
        margin-bottom: 1rem;
    }

    .mb-6 {
        margin-bottom: 1.5rem;
    }

    .mt-1 {
        margin-top: 0.25rem;
    }

    .mt-3 {
        margin-top: 0.75rem;
    }

    .mt-4 {
        margin-top: 1rem;
    }

    .py-8 {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }

    .text-4xl {
        font-size: 2.25rem;
    }

    .text-lg {
        font-size: 1.125rem;
    }

    .text-sm {
        font-size: 0.875rem;
    }

    .text-xs {
        font-size: 0.75rem;
    }

    .text-2xl {
        font-size: 1.5rem;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .flex {
        display: flex;
    }

    .grid {
        display: grid;
    }

    .hidden {
        display: none;
    }

    .block {
        display: block;
    }

    .w-full {
        width: 100%;
    }

    .bg-gray-200 {
        background-color: #e5e7eb;
    }

    .bg-blue-600 {
        background-color: #2563eb;
    }

    .rounded-full {
        border-radius: 9999px;
    }

    .h-2 {
        height: 0.5rem;
    }

    .min-100 {
        min-width: 100%;
    }

    /* 2-column stats grid */
    .stats-grid-2col {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Smaller stat cards for card layout */
    .card-body .stat-card {
        padding: 0.75rem;
    }

    .card-body .stat-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .card-body .stat-value {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .card-body .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
    }

    /* Financial overview grid - 2 cards side by side */
    .financial-overview-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .financial-overview-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection