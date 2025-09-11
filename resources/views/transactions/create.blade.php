@extends('layouts.app')

@section('title', 'Add Transaction')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Add Transaction</h1>
                <p class="page-subtitle">Record a new income or expense</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Transaction Form -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus"></i> Transaction Details
        </div>
        <div class="card-body">
            <form id="transactionForm" method="POST" action="{{ route('transactions.store') }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Transaction Type</label>
                    <select class="form-select" name="type" id="transactionType" required>
                        <option value="">Select Type</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ currency_symbol() }}</span>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0.01" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id" id="categorySelect" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" data-type="{{ $category->type }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Account</label>
                    <select class="form-select" name="account_id" required>
                        <option value="">Select Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea class="form-control" name="description" rows="3" placeholder="Add a note about this transaction"></textarea>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Transaction
                    </button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('transactionType');
    const categorySelect = document.getElementById('categorySelect');
    
    // Filter categories based on transaction type
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        const options = categorySelect.querySelectorAll('option[data-type]');
        
        // Reset category selection
        categorySelect.value = '';
        
        // Show/hide category options based on type
        options.forEach(option => {
            if (selectedType === '' || option.dataset.type === selectedType) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });
    
    // Handle form submission
    document.getElementById('transactionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert('Transaction added successfully!');
                // Redirect to transactions list
                window.location.href = '{{ route("transactions.index") }}';
            } else {
                // Show error message
                alert('Error: ' + (data.message || 'Failed to add transaction'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the transaction');
        });
    });
});
</script>
@endsection
