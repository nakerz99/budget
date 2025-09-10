@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Budget Planning</h1>
                <div class="d-flex gap-2">
                    <!-- Month Selector -->
                    <select class="form-select" id="monthSelector" style="width: auto;">
                        @foreach($availableMonths as $month)
                            <option value="{{ $month['value'] }}" {{ $selectedMonth == $month['value'] ? 'selected' : '' }}>
                                {{ $month['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary btn-sm" id="saveBudgetBtn">
                        <i class="fas fa-save"></i> Save Budget
                    </button>
                    <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#copyBudgetModal">
                        <i class="fas fa-copy"></i> Copy from Previous
                    </button>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Monthly Income</div>
                            <div class="h4 mb-0 text-success">{{ currency_symbol() }}{{ number_format($summary['monthly_income'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Total Budget</div>
                            <div class="h4 mb-0">{{ currency_symbol() }}{{ number_format($summary['total_budget'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Total Spent</div>
                            <div class="h4 mb-0 text-danger">{{ currency_symbol() }}{{ number_format($summary['total_spent'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Remaining</div>
                            <div class="h4 mb-0 {{ $summary['total_remaining'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ currency_symbol() }}{{ number_format(abs($summary['total_remaining']), 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Budget Used</div>
                            <div class="h4 mb-0">{{ $summary['percentage_used'] }}%</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Net Savings</div>
                            <div class="h4 mb-0 {{ $summary['net_savings'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ currency_symbol() }}{{ number_format($summary['net_savings'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Categories -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Category Budgets for {{ $monthDate->format('F Y') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="border-0">Category</th>
                            <th class="border-0">Budget Amount</th>
                            <th class="border-0">Spent</th>
                            <th class="border-0">Remaining</th>
                            <th class="border-0">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budgetData as $data)
                        <tr>
                            <td>
                                <span class="badge" style="background-color: {{ $data['category']->color }};">
                                    @if($data['category']->icon)
                                        <i class="fas fa-{{ $data['category']->icon }}"></i>
                                    @endif
                                    {{ $data['category']->name }}
                                </span>
                            </td>
                            <td>
                                <div class="input-group" style="width: 150px;">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control budget-amount" 
                                           data-category-id="{{ $data['category']->id }}"
                                           value="{{ $data['budget_amount'] }}"
                                           step="0.01"
                                           min="0">
                                </div>
                            </td>
                            <td class="text-danger">
                                {{ currency_symbol() }}{{ number_format($data['spent'], 2) }}
                            </td>
                            <td class="{{ $data['remaining'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ currency_symbol() }}{{ number_format(abs($data['remaining']), 2) }}
                                @if($data['remaining'] < 0)
                                    <small>(Over)</small>
                                @endif
                            </td>
                            <td style="width: 300px;">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                        <div class="progress-bar {{ $data['percentage'] > 100 ? 'bg-danger' : ($data['percentage'] > 80 ? 'bg-warning' : 'bg-success') }}" 
                                             role="progressbar" 
                                             style="width: {{ min($data['percentage'], 100) }}%">
                                            {{ $data['percentage'] }}%
                                        </div>
                                    </div>
                                    @if($data['percentage'] > 100)
                                        <span class="text-danger small">Over!</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No expense categories found. Create categories in Settings to start budgeting.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($budgetData) > 0)
                    <tfoot>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td>{{ currency_symbol() }}{{ number_format($summary['total_budget'], 2) }}</td>
                            <td class="text-danger">{{ currency_symbol() }}{{ number_format($summary['total_spent'], 2) }}</td>
                            <td class="{{ $summary['total_remaining'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ currency_symbol() }}{{ number_format(abs($summary['total_remaining']), 2) }}
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $summary['percentage_used'] > 100 ? 'bg-danger' : ($summary['percentage_used'] > 80 ? 'bg-warning' : 'bg-success') }}" 
                                         role="progressbar" 
                                         style="width: {{ min($summary['percentage_used'], 100) }}%">
                                        {{ $summary['percentage_used'] }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Budget Tips -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Budget Tips</h5>
                    <ul class="mb-0">
                        <li>Follow the 50/30/20 rule: 50% needs, 30% wants, 20% savings</li>
                        <li>Review and adjust your budget monthly</li>
                        <li>Set realistic budget amounts based on past spending</li>
                        <li>Leave some buffer for unexpected expenses</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Budget Status</h5>
                    @if($summary['percentage_used'] > 100)
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-triangle"></i> You've exceeded your budget by {{ currency_symbol() }}{{ number_format(abs($summary['total_remaining']), 2) }}!
                        </div>
                    @elseif($summary['percentage_used'] > 80)
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-circle"></i> You've used {{ $summary['percentage_used'] }}% of your budget. Be careful with spending!
                        </div>
                    @else
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle"></i> Great job! You're on track with your budget.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Copy Budget Modal -->
<div class="modal fade" id="copyBudgetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="copyBudgetForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Copy Budget from Previous Month</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Copy From</label>
                        <select class="form-select" name="from_month" required>
                            <option value="">Select Month</option>
                            @foreach($availableMonths as $month)
                                @if($month['value'] != $selectedMonth)
                                    <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This will copy all budget amounts from the selected month to {{ $monthDate->format('F Y') }}.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Copy Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Month selector change
    $('#monthSelector').on('change', function() {
        window.location.href = '{{ route("budget.index") }}?month=' + $(this).val();
    });
    
    // Save budget
    $('#saveBudgetBtn').on('click', function() {
        const budgets = [];
        $('.budget-amount').each(function() {
            budgets.push({
                category_id: $(this).data('category-id'),
                amount: parseFloat($(this).val()) || 0
            });
        });
        
        $.ajax({
            url: '{{ route("budget.store") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                month: '{{ $selectedMonth }}',
                budgets: budgets
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Failed to save budget'));
            }
        });
    });
    
    // Copy budget
    $('#copyBudgetForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("budget.copy") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                from_month: $('[name="from_month"]').val(),
                to_month: '{{ $selectedMonth }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.error || 'Failed to copy budget'));
            }
        });
    });
    
    // Auto-calculate totals when budget amounts change
    $('.budget-amount').on('input', function() {
        let totalBudget = 0;
        $('.budget-amount').each(function() {
            totalBudget += parseFloat($(this).val()) || 0;
        });
        // You could update the total display here if needed
    });
});
</script>
@endsection
