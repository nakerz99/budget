@extends('layouts.app')

@section('title', 'Edit Bill')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Edit Bill</h1>
                <p class="page-subtitle">Update bill details</p>
            </div>
            <a href="{{ route('bills.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Bill Form -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit"></i> Bill Details
        </div>
        <div class="card-body">
            <form id="billForm" method="POST" action="{{ route('bills.update', $bill) }}">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label class="form-label">Bill Name</label>
                    <input type="text" name="name" class="form-control" 
                           placeholder="e.g., Electricity Bill" value="{{ $bill->name }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ currency_symbol() }}</span>
                        <input type="number" name="amount" class="form-control" 
                               placeholder="0.00" step="0.01" min="0" value="{{ $bill->amount }}" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" 
                           value="{{ $bill->due_date->format('Y-m-d') }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $bill->category_id === $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Frequency</label>
                    <select name="frequency" class="form-select">
                        <option value="once" {{ $bill->frequency === 'once' ? 'selected' : '' }}>One-time</option>
                        <option value="weekly" {{ $bill->frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $bill->frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ $bill->frequency === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="is_recurring" value="1" {{ $bill->is_recurring ? 'checked' : '' }} class="mr-2">
                        Recurring Bill
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3" 
                              placeholder="Add any additional notes about this bill">{{ $bill->notes }}</textarea>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Bill
                    </button>
                    <a href="{{ route('bills.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission
    document.getElementById('billForm').addEventListener('submit', function(e) {
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
                alert('Bill updated successfully!');
                // Redirect to bills list
                window.location.href = '{{ route("bills.index") }}';
            } else {
                // Show error message
                alert('Error: ' + (data.message || 'Failed to update bill'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the bill');
        });
    });
});
</script>
@endsection
