@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Financial Reports</h1>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('reports.index') }}" class="d-flex gap-2">
                        <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" max="{{ date('Y-m-d') }}">
                        <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" max="{{ date('Y-m-d') }}">
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    </form>
                    <a href="{{ route('reports.export.csv') }}?start_date={{ $startDate }}&end_date={{ $endDate }}" class="btn btn-success btn-sm">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="text-center">
                                <div class="text-muted small">Total Income</div>
                                <div class="h4 mb-0 text-success">{{ currency_symbol() }}{{ number_format($summary['total_income'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-center">
                                <div class="text-muted small">Total Expenses</div>
                                <div class="h4 mb-0 text-danger">{{ currency_symbol() }}{{ number_format($summary['total_expenses'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-center">
                                <div class="text-muted small">Net Income</div>
                                <div class="h4 mb-0 {{ $summary['net_income'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ currency_symbol() }}{{ number_format(abs($summary['net_income']), 2) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-center">
                                <div class="text-muted small">Savings Rate</div>
                                <div class="h4 mb-0">{{ $summary['savings_rate'] }}%</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="text-center">
                                <div class="text-muted small">Net Worth</div>
                                <div class="h5 mb-0 text-primary">{{ currency_symbol() }}{{ number_format($summary['net_worth'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-center">
                                <div class="text-muted small">Total Savings</div>
                                <div class="h5 mb-0">{{ currency_symbol() }}{{ number_format($summary['total_savings'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-center">
                                <div class="text-muted small">Monthly Bills</div>
                                <div class="h5 mb-0">{{ currency_symbol() }}{{ number_format($summary['monthly_bills'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-center">
                                <div class="text-muted small">Avg Daily Spending</div>
                                <div class="h5 mb-0">{{ currency_symbol() }}{{ number_format($summary['avg_daily_spending'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <!-- Income vs Expenses -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Income vs Expenses</h5>
                </div>
                <div class="card-body" style="height: 300px; position: relative;">
                    <canvas id="incomeVsExpensesChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Category Breakdown -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Expense Categories</h5>
                </div>
                <div class="card-body" style="height: 300px; position: relative;">
                    <canvas id="categoryBreakdownChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">6-Month Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <!-- Budget Performance -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Budget Performance</h5>
                </div>
                <div class="card-body">
                    @if(count($budgetPerformance) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th class="text-end">Budget</th>
                                        <th class="text-end">Spent</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($budgetPerformance as $budget)
                                    <tr>
                                        <td>{{ $budget['category'] }}</td>
                                        <td class="text-end">{{ currency_symbol() }}{{ number_format($budget['budget'], 2) }}</td>
                                        <td class="text-end">{{ currency_symbol() }}{{ number_format($budget['spent'], 2) }}</td>
                                        <td class="text-end">
                                            <span class="badge {{ $budget['percentage'] > 100 ? 'bg-danger' : ($budget['percentage'] > 80 ? 'bg-warning' : 'bg-success') }}">
                                                {{ $budget['percentage'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No budget data for this period</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Savings Progress -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Savings Goals Progress</h5>
                </div>
                <div class="card-body">
                    @if($savingsProgress->count() > 0)
                        @foreach($savingsProgress as $goal)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-medium">{{ $goal->name }}</span>
                                <span class="small text-muted">{{ $goal->progress_percentage }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $goal->progress_percentage }}%; background-color: {{ $goal->color }}"
                                     aria-valuenow="{{ $goal->progress_percentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">{{ currency_symbol() }}{{ number_format($goal->current_amount, 2) }}</small>
                                <small class="text-muted">{{ currency_symbol() }}{{ number_format($goal->target_amount, 2) }}</small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No active savings goals</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="row mb-4">
        <!-- Top Expenses -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top 10 Expenses</h5>
                </div>
                <div class="card-body">
                    @if($topExpenses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topExpenses as $expense)
                                    <tr>
                                        <td>{{ $expense->transaction_date->format('M d') }}</td>
                                        <td>{{ Str::limit($expense->description ?: 'N/A', 20) }}</td>
                                        <td>{{ $expense->category->name }}</td>
                                        <td class="text-end text-danger">{{ currency_symbol() }}{{ number_format(abs($expense->amount), 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No expenses in this period</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Account Balances -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Account Balances</h5>
                </div>
                <div class="card-body">
                    @if($accountBalances->count() > 0)
                        <div class="account-balances-list">
                            @foreach($accountBalances as $account)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <div class="fw-medium">{{ $account->name }}</div>
                                    <small class="text-muted">{{ ucfirst($account->type) }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="h5 mb-0 {{ $account->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ currency_symbol() }}{{ number_format(abs($account->balance), 2) }}
                                    </div>
                                    @if($account->balance < 0)
                                        <small class="text-danger">Overdrawn</small>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold">Total Net Worth</div>
                                    <div class="h4 mb-0 {{ $accountBalances->sum('balance') >= 0 ? 'text-primary' : 'text-danger' }}">
                                        {{ currency_symbol() }}{{ number_format(abs($accountBalances->sum('balance')), 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted text-center">No accounts found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Income vs Expenses Chart
const incomeVsExpensesCtx = document.getElementById('incomeVsExpensesChart').getContext('2d');
new Chart(incomeVsExpensesCtx, {
    type: 'doughnut',
    data: {
        labels: ['Income', 'Expenses'],
        datasets: [{
            data: [{{ $incomeVsExpenses['income'] }}, {{ $incomeVsExpenses['expenses'] }}],
            backgroundColor: ['#10B981', '#EF4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 10,
                    font: {
                        size: 12
                    }
                }
            }
        },
        layout: {
            padding: 10
        }
    }
});

// Category Breakdown Chart
const categoryBreakdownCtx = document.getElementById('categoryBreakdownChart').getContext('2d');
new Chart(categoryBreakdownCtx, {
    type: 'pie',
    data: {
        labels: {!! json_encode($categoryBreakdown->pluck('name')) !!},
        datasets: [{
            data: {!! json_encode($categoryBreakdown->pluck('total')) !!},
            backgroundColor: {!! json_encode($categoryBreakdown->pluck('color')) !!},
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Monthly Trend Chart
const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
new Chart(monthlyTrendCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(collect($monthlyTrend)->pluck('month')) !!},
        datasets: [{
            label: 'Income',
            data: {!! json_encode(collect($monthlyTrend)->pluck('income')) !!},
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4
        }, {
            label: 'Expenses',
            data: {!! json_encode(collect($monthlyTrend)->pluck('expenses')) !!},
            borderColor: '#EF4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

</script>
@endsection