@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Financial Reports</h1>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total Income</div>
                    <div class="h4 mb-0 text-success">{{ currency_symbol() }}{{ number_format($summary['total_income'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total Expenses</div>
                    <div class="h4 mb-0 text-danger">{{ currency_symbol() }}{{ number_format($summary['total_expenses'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Net Income</div>
                    <div class="h4 mb-0 {{ $summary['net_income'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ currency_symbol() }}{{ number_format($summary['net_income'], 2) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Net Worth</div>
                    <div class="h4 mb-0 text-primary">{{ currency_symbol() }}{{ number_format($summary['net_worth'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Charts -->
    <div class="row mb-4">
        <!-- Income vs Expenses -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Income vs Expenses</h5>
                </div>
                <div class="card-body">
                    <div style="max-width: 300px; margin: 0 auto;">
                        <canvas id="incomeVsExpensesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Expenses -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top Expenses</h5>
                </div>
                <div class="card-body">
                    @if($topExpenses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    @foreach($topExpenses->take(5) as $expense)
                                    <tr>
                                        <td>{{ $expense->description }}</td>
                                        <td class="text-end">{{ currency_symbol() }}{{ number_format($expense->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No expenses found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Account Balances -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Account Balances</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($accountBalances as $account)
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <div class="text-muted small">{{ $account->name }}</div>
                                <div class="h5 mb-0">{{ currency_symbol() }}{{ number_format($account->balance, 2) }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Simple Income vs Expenses Chart
const ctx = document.getElementById('incomeVsExpensesChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Income', 'Expenses'],
        datasets: [{
            data: [{{ $incomeVsExpenses['income'] }}, {{ $incomeVsExpenses['expenses'] }}],
            backgroundColor: ['#10B981', '#EF4444']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endsection
