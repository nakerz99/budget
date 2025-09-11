@extends('layouts.app')

@section('title', 'Financial Reports')

@section('content')
<div>
    <!-- Header with integrated summary -->
    <div class="mb-4 lg:mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-3 lg:mb-4">
            <!-- Left side: Title and controls -->
            <div class="flex-1">
                <h1 class="text-xl lg:text-2xl font-bold text-gray-800 mb-3">Financial Reports</h1>
                
                <!-- Date Range and Export -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col sm:flex-row gap-2 flex-1">
                        <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" max="{{ date('Y-m-d') }}">
                        <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" max="{{ date('Y-m-d') }}">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i>
                            <span class="hidden sm:inline ml-1">Apply</span>
                        </button>
                    </form>
                    <a href="{{ route('reports.export.csv') }}?start_date={{ $startDate }}&end_date={{ $endDate }}" class="btn btn-success btn-sm">
                        <i class="fas fa-download"></i>
                        <span class="hidden sm:inline ml-1">Export CSV</span>
                        <span class="sm:hidden ml-1">Export</span>
                    </a>
                </div>
            </div>
            
            <!-- Right side: Financial Summary -->
            <div class="financial-summary-inline">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-3">
                    <div class="stat-card-inline income">
                        <div class="stat-icon-inline">📈</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-green-600">{{ currency_symbol() }}{{ number_format($summary['total_income'], 0) }}</div>
                            <div class="stat-label-inline">Income</div>
                        </div>
                    </div>

                    <div class="stat-card-inline expense">
                        <div class="stat-icon-inline">📉</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-red-600">{{ currency_symbol() }}{{ number_format($summary['total_expenses'], 0) }}</div>
                            <div class="stat-label-inline">Expenses</div>
                        </div>
                    </div>

                    <div class="stat-card-inline balance">
                        <div class="stat-icon-inline">💰</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline {{ $summary['net_income'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ currency_symbol() }}{{ number_format(abs($summary['net_income']), 0) }}</div>
                            <div class="stat-label-inline">Net</div>
                        </div>
                    </div>

                    <div class="stat-card-inline">
                        <div class="stat-icon-inline">📊</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-blue-600">{{ $summary['savings_rate'] }}%</div>
                            <div class="stat-label-inline">Savings</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-600 mb-1">Net Worth</div>
            <div class="text-lg font-semibold text-gray-800">{{ currency_symbol() }}{{ number_format($summary['net_worth'], 0) }}</div>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-600 mb-1">Total Savings</div>
            <div class="text-lg font-semibold text-gray-800">{{ currency_symbol() }}{{ number_format($summary['total_savings'], 0) }}</div>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-600 mb-1">Monthly Bills</div>
            <div class="text-lg font-semibold text-gray-800">{{ currency_symbol() }}{{ number_format($summary['monthly_bills'], 0) }}</div>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-600 mb-1">Avg Daily Spending</div>
            <div class="text-lg font-semibold text-gray-800">{{ currency_symbol() }}{{ number_format($summary['avg_daily_spending'], 0) }}</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Income vs Expenses Chart -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Income vs Expenses
            </div>
            <div class="card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="incomeVsExpensesChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Category Breakdown Chart -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Expense Categories
            </div>
            <div class="card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="categoryBreakdownChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend Chart -->
    <div class="card mb-6">
        <div class="card-header">
            <i class="fas fa-chart-line"></i> 6-Month Trend
        </div>
        <div class="card-body">
            <div style="height: 400px; position: relative;">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Budget Performance -->
    @if(isset($budgetPerformance) && count($budgetPerformance) > 0)
    <div class="card mb-6">
        <div class="card-header">
            <i class="fas fa-target"></i> Budget Performance
        </div>
        <div class="card-body">
            <div class="space-y-3">
                @foreach($budgetPerformance as $category => $data)
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <!-- Category Info -->
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800 text-lg">{{ $category }}</div>
                            <div class="text-sm text-gray-600 mt-1">
                                {{ $data['percentage'] }}% of budget used
                            </div>
                        </div>
                        
                        <!-- Budget vs Spent -->
                        <div class="flex flex-col sm:items-end gap-2">
                            <div class="flex gap-4 text-sm">
                                <div class="text-center">
                                    <div class="text-gray-600">Budget</div>
                                    <div class="font-semibold">{{ currency_symbol() }}{{ number_format($data['budget'], 0) }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-gray-600">Spent</div>
                                    <div class="font-semibold {{ $data['spent'] > $data['budget'] ? 'text-red-600' : 'text-green-600' }}">
                                        {{ currency_symbol() }}{{ number_format($data['spent'], 0) }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-gray-600">Remaining</div>
                                    <div class="font-semibold {{ $data['remaining'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ currency_symbol() }}{{ number_format($data['remaining'], 0) }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full sm:w-64">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $data['percentage'] > 100 ? 'bg-red-500' : ($data['percentage'] > 80 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                         style="width: {{ min($data['percentage'], 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif


    <!-- Category Analysis -->
    @if(isset($categoryBreakdown) && count($categoryBreakdown) > 0)
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-pie"></i> Category Analysis
        </div>
        <div class="card-body">
            <div class="space-y-4">
                @foreach($categoryBreakdown as $category)
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                                 style="background: {{ $category->color }}20; color: {{ $category->color }};">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800 text-lg">{{ $category->name }}</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    {{ currency_symbol() }}{{ number_format($category->total, 0) }} total
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:items-end gap-2">
                            <div class="text-xl font-bold text-gray-800">
                                {{ currency_symbol() }}{{ number_format($category->total, 0) }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ round(($category->total / $summary['total_expenses']) * 100, 1) }}% of expenses
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Chart.js loaded, initializing charts...');
    
    // Income vs Expenses Chart
    const incomeVsExpensesCtx = document.getElementById('incomeVsExpensesChart');
    if (incomeVsExpensesCtx) {
        console.log('Creating income vs expenses chart...');
        new Chart(incomeVsExpensesCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Income', 'Expenses'],
                datasets: [{
                    data: [{{ $summary['total_income'] ?? 0 }}, {{ $summary['total_expenses'] ?? 0 }}],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderColor: ['#059669', '#dc2626'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '{{ currency_symbol() }}' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Category Breakdown Chart
    const categoryBreakdownCtx = document.getElementById('categoryBreakdownChart');
    if (categoryBreakdownCtx) {
        console.log('Creating category breakdown chart...');
        const categoryData = {!! json_encode(isset($categoryBreakdown) ? $categoryBreakdown : collect()) !!};
        console.log('Category data:', categoryData);
        
        if (categoryData.length > 0) {
            new Chart(categoryBreakdownCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(cat => cat.name),
                    datasets: [{
                        data: categoryData.map(cat => cat.total),
                        backgroundColor: categoryData.map(cat => cat.color)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        } else {
            // Show empty state
            categoryBreakdownCtx.parentElement.innerHTML = '<div class="text-center py-8 text-gray-500">No expense data available for the selected period</div>';
        }
    }

    // Monthly Trend Chart
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx) {
        console.log('Creating monthly trend chart...');
        const trendData = {!! json_encode(isset($monthlyTrend) ? $monthlyTrend : []) !!};
        console.log('Monthly trend data:', trendData);
        
        if (trendData.length > 0) {
            new Chart(monthlyTrendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendData.map(item => item.month),
                    datasets: [{
                        label: 'Income',
                        data: trendData.map(item => item.income),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Expenses',
                        data: trendData.map(item => item.expenses),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '{{ currency_symbol() }}' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // Show empty state
            monthlyTrendCtx.parentElement.innerHTML = '<div class="text-center py-8 text-gray-500">No trend data available</div>';
        }
    }
});
</script>
@endsection