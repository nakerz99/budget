@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3">Transactions</h1>
            
            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Total Income</div>
                            <div class="h4 mb-0 text-success">{{ currency_symbol() }}{{ number_format($summary['total_income'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Total Expenses</div>
                            <div class="h4 mb-0 text-danger">{{ currency_symbol() }}{{ number_format(abs($summary['total_expense']), 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Net Amount</div>
                            <div class="h4 mb-0 {{ $summary['net_amount'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ currency_symbol() }}{{ number_format($summary['net_amount'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Transactions</div>
                            <div class="h4 mb-0">{{ number_format($summary['transaction_count']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('transactions.index') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Date Range -->
                    <div class="col-md-2">
                        <label class="form-label small">From Date</label>
                        <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">To Date</label>
                        <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="col-md-2">
                        <label class="form-label small">Category</label>
                        <select class="form-select" name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $filters['category_id'] == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Account Filter -->
                    <div class="col-md-2">
                        <label class="form-label small">Account</label>
                        <select class="form-select" name="account_id">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $filters['account_id'] == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Type Filter -->
                    <div class="col-md-2">
                        <label class="form-label small">Type</label>
                        <select class="form-select" name="type">
                            <option value="">All Types</option>
                            <option value="income" {{ $filters['type'] == 'income' ? 'selected' : '' }}>Income</option>
                            <option value="expense" {{ $filters['type'] == 'expense' ? 'selected' : '' }}>Expense</option>
                            <option value="transfer" {{ $filters['type'] == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                    
                    <!-- Search -->
                    <div class="col-md-2">
                        <label class="form-label small">Search</label>
                        <input type="text" class="form-control" name="search" placeholder="Search..." value="{{ $filters['search'] }}">
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12 d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                                <i class="fas fa-plus"></i> Add Transaction
                            </button>
                            <button type="button" class="btn btn-danger btn-sm d-none" id="bulkDeleteBtn">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="border-0">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th class="border-0">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'transaction_date', 'order' => $filters['sort'] == 'transaction_date' && $filters['order'] == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                    Date
                                    @if($filters['sort'] == 'transaction_date')
                                        <i class="fas fa-sort-{{ $filters['order'] == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="border-0">Description</th>
                            <th class="border-0">Category</th>
                            <th class="border-0">Account</th>
                            <th class="border-0">Type</th>
                            <th class="border-0 text-end">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'amount', 'order' => $filters['sort'] == 'amount' && $filters['order'] == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                    Amount
                                    @if($filters['sort'] == 'amount')
                                        <i class="fas fa-sort-{{ $filters['order'] == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input transaction-checkbox" value="{{ $transaction->id }}">
                            </td>
                            <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                            <td>{{ $transaction->description ?: '-' }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $transaction->category->color }};">
                                    {{ $transaction->category->name }}
                                </span>
                            </td>
                            <td>{{ $transaction->account->name }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->type == 'income' ? 'success' : ($transaction->type == 'expense' ? 'danger' : 'info') }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="text-end fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ currency_symbol() }}{{ number_format(abs($transaction->amount), 2) }}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary edit-transaction" 
                                        data-transaction='@json($transaction)'
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editTransactionModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-transaction" 
                                        data-id="{{ $transaction->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No transactions found. Start by adding your first transaction!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($transactions->hasPages())
        <div class="card-footer border-0 bg-white">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addTransactionForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Transaction Type -->
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="typeIncome" value="income">
                            <label class="btn btn-outline-success" for="typeIncome">Income</label>
                            
                            <input type="radio" class="btn-check" name="type" id="typeExpense" value="expense" checked>
                            <label class="btn btn-outline-danger" for="typeExpense">Expense</label>
                            
                            <input type="radio" class="btn-check" name="type" id="typeTransfer" value="transfer">
                            <label class="btn btn-outline-info" for="typeTransfer">Transfer</label>
                        </div>
                    </div>
                    
                    <!-- Amount -->
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="amount" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Account -->
                    <div class="mb-3">
                        <label class="form-label">Account</label>
                        <select class="form-select" name="account_id" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ currency_symbol() }}{{ number_format($account->balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Date -->
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <input type="text" class="form-control" name="description" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editTransactionForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="transaction_id" id="editTransactionId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Same fields as Add Modal but with edit_ prefix -->
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="editTypeIncome" value="income">
                            <label class="btn btn-outline-success" for="editTypeIncome">Income</label>
                            
                            <input type="radio" class="btn-check" name="type" id="editTypeExpense" value="expense">
                            <label class="btn btn-outline-danger" for="editTypeExpense">Expense</label>
                            
                            <input type="radio" class="btn-check" name="type" id="editTypeTransfer" value="transfer">
                            <label class="btn btn-outline-info" for="editTypeTransfer">Transfer</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="amount" id="editAmount" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id" id="editCategory" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Account</label>
                        <select class="form-select" name="account_id" id="editAccount" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ currency_symbol() }}{{ number_format($account->balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="transaction_date" id="editDate" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <input type="text" class="form-control" name="description" id="editDescription" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Select All checkbox
    $('#selectAll').on('change', function() {
        $('.transaction-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkDelete();
    });
    
    // Individual checkboxes
    $('.transaction-checkbox').on('change', function() {
        toggleBulkDelete();
    });
    
    function toggleBulkDelete() {
        const checkedCount = $('.transaction-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteBtn').removeClass('d-none');
        } else {
            $('#bulkDeleteBtn').addClass('d-none');
        }
    }
    
    // Add Transaction
    $('#addTransactionForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("transactions.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
    
    // Edit Transaction
    $('.edit-transaction').on('click', function() {
        const transaction = $(this).data('transaction');
        $('#editTransactionId').val(transaction.id);
        $(`#editType${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}`).prop('checked', true);
        $('#editAmount').val(Math.abs(transaction.amount));
        $('#editCategory').val(transaction.category_id);
        $('#editAccount').val(transaction.account_id);
        $('#editDate').val(transaction.transaction_date);
        $('#editDescription').val(transaction.description);
    });
    
    $('#editTransactionForm').on('submit', function(e) {
        e.preventDefault();
        const transactionId = $('#editTransactionId').val();
        
        $.ajax({
            url: `/transactions/${transactionId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
    
    // Delete Transaction
    $('.delete-transaction').on('click', function() {
        if (!confirm('Are you sure you want to delete this transaction?')) {
            return;
        }
        
        const transactionId = $(this).data('id');
        
        $.ajax({
            url: `/transactions/${transactionId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
    
    // Bulk Delete
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = $('.transaction-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedIds.length === 0) {
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${selectedIds.length} transactions?`)) {
            return;
        }
        
        $.ajax({
            url: '{{ route("transactions.bulkDelete") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                transaction_ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
});
</script>
@endsection
