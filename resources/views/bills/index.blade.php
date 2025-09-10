@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Bills & Subscriptions</h1>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBillModal">
                    <i class="fas fa-plus"></i> Add Bill
                </button>
            </div>
            
            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Monthly Bills</div>
                            <div class="h4 mb-0">{{ currency_symbol() }}{{ number_format($summary['total_monthly'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Due This Week</div>
                            <div class="h4 mb-0 text-warning">{{ currency_symbol() }}{{ number_format($summary['due_this_week'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Overdue</div>
                            <div class="h4 mb-0 text-danger">{{ currency_symbol() }}{{ number_format($summary['overdue_amount'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Upcoming Bills</div>
                            <div class="h4 mb-0">{{ $summary['upcoming_count'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Bills Alert -->
    @if($overdueBills->count() > 0)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Attention!</strong> You have {{ $overdueBills->count() }} overdue bill(s) totaling {{ currency_symbol() }}{{ number_format($overdueBills->sum('amount'), 2) }}.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Upcoming Bills -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Upcoming Bills (Next 30 Days)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="border-0">Due Date</th>
                            <th class="border-0">Bill Name</th>
                            <th class="border-0">Category</th>
                            <th class="border-0">Amount</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingBills as $bill)
                        <tr class="{{ $bill->due_date->isPast() ? 'table-danger' : ($bill->due_date->diffInDays(now()) <= 3 ? 'table-warning' : '') }}">
                            <td>
                                {{ $bill->due_date->format('M d, Y') }}
                                @if($bill->due_date->isToday())
                                    <span class="badge bg-danger">Today</span>
                                @elseif($bill->due_date->isTomorrow())
                                    <span class="badge bg-warning">Tomorrow</span>
                                @elseif($bill->due_date->diffInDays(now()) <= 3)
                                    <span class="badge bg-info">{{ $bill->due_date->diffInDays(now()) }} days</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $bill->name }}</strong>
                                @if($bill->is_recurring)
                                    <span class="badge bg-secondary">{{ ucfirst($bill->frequency) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $bill->category->color }};">
                                    {{ $bill->category->name }}
                                </span>
                            </td>
                            <td class="fw-bold">{{ currency_symbol() }}{{ number_format($bill->amount, 2) }}</td>
                            <td>
                                @if($bill->is_paid)
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!$bill->is_paid)
                                    <button class="btn btn-sm btn-success mark-paid" 
                                            data-bill-id="{{ $bill->id }}"
                                            data-bill-name="{{ $bill->name }}"
                                            data-bill-amount="{{ $bill->amount }}">
                                        <i class="fas fa-check"></i> Pay
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary mark-unpaid" 
                                            data-bill-id="{{ $bill->id }}">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No upcoming bills in the next 30 days.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- All Bills Management -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">All Bills</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="border-0">Bill Name</th>
                            <th class="border-0">Category</th>
                            <th class="border-0">Amount</th>
                            <th class="border-0">Due Date</th>
                            <th class="border-0">Frequency</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allBills as $bill)
                        <tr>
                            <td>
                                <strong>{{ $bill->name }}</strong>
                                @if($bill->notes)
                                    <br><small class="text-muted">{{ $bill->notes }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $bill->category->color }};">
                                    {{ $bill->category->name }}
                                </span>
                            </td>
                            <td class="fw-bold">{{ currency_symbol() }}{{ number_format($bill->amount, 2) }}</td>
                            <td>{{ $bill->due_date->format('M d, Y') }}</td>
                            <td>
                                @if($bill->is_recurring)
                                    <span class="badge bg-info">{{ ucfirst($bill->frequency) }}</span>
                                @else
                                    <span class="badge bg-secondary">One-time</span>
                                @endif
                            </td>
                            <td>
                                @if($bill->is_paid)
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary edit-bill" 
                                        data-bill='@json($bill)'
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editBillModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-bill" 
                                        data-id="{{ $bill->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No bills found. Start by adding your first bill!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($allBills->hasPages())
        <div class="card-footer border-0 bg-white">
            {{ $allBills->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Bill Modal -->
<div class="modal fade" id="addBillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addBillForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Bill Name -->
                    <div class="mb-3">
                        <label class="form-label">Bill Name</label>
                        <input type="text" class="form-control" name="name" required>
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
                    
                    <!-- Due Date -->
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date" required>
                    </div>
                    
                    <!-- Recurring -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_recurring" id="isRecurring">
                            <label class="form-check-label" for="isRecurring">
                                This is a recurring bill
                            </label>
                        </div>
                    </div>
                    
                    <!-- Frequency (shown only if recurring) -->
                    <div class="mb-3 d-none" id="frequencyGroup">
                        <label class="form-label">Frequency</label>
                        <select class="form-select" name="frequency">
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    
                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Bill Modal -->
<div class="modal fade" id="editBillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editBillForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="bill_id" id="editBillId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Same fields as Add Modal but with edit_ prefix -->
                    <div class="mb-3">
                        <label class="form-label">Bill Name</label>
                        <input type="text" class="form-control" name="name" id="editName" required>
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
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date" id="editDueDate" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_recurring" id="editIsRecurring">
                            <label class="form-check-label" for="editIsRecurring">
                                This is a recurring bill
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3 d-none" id="editFrequencyGroup">
                        <label class="form-label">Frequency</label>
                        <select class="form-select" name="frequency" id="editFrequency">
                            <option value="monthly">Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" id="editNotes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pay Bill Modal -->
<div class="modal fade" id="payBillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="payBillForm">
                @csrf
                <input type="hidden" name="bill_id" id="payBillId">
                <div class="modal-header">
                    <h5 class="modal-title">Pay Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Mark <strong id="payBillName"></strong> as paid?</p>
                    <p class="text-muted">Amount: $<span id="payBillAmount"></span></p>
                    
                    <!-- Account Selection -->
                    <div class="mb-3">
                        <label class="form-label">Pay From Account</label>
                        <select class="form-select" name="account_id" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->name }} ({{ currency_symbol() }}{{ number_format($account->balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Payment Date -->
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Toggle frequency field based on recurring checkbox
    $('#isRecurring').on('change', function() {
        if ($(this).is(':checked')) {
            $('#frequencyGroup').removeClass('d-none');
        } else {
            $('#frequencyGroup').addClass('d-none');
        }
    });
    
    $('#editIsRecurring').on('change', function() {
        if ($(this).is(':checked')) {
            $('#editFrequencyGroup').removeClass('d-none');
        } else {
            $('#editFrequencyGroup').addClass('d-none');
        }
    });
    
    // Add Bill
    $('#addBillForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("bills.store") }}',
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
    
    // Edit Bill
    $('.edit-bill').on('click', function() {
        const bill = $(this).data('bill');
        $('#editBillId').val(bill.id);
        $('#editName').val(bill.name);
        $('#editAmount').val(bill.amount);
        $('#editCategory').val(bill.category_id);
        $('#editDueDate').val(bill.due_date);
        $('#editIsRecurring').prop('checked', bill.is_recurring);
        $('#editFrequency').val(bill.frequency);
        $('#editNotes').val(bill.notes);
        
        if (bill.is_recurring) {
            $('#editFrequencyGroup').removeClass('d-none');
        } else {
            $('#editFrequencyGroup').addClass('d-none');
        }
    });
    
    $('#editBillForm').on('submit', function(e) {
        e.preventDefault();
        const billId = $('#editBillId').val();
        
        $.ajax({
            url: `/bills/${billId}`,
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
    
    // Delete Bill
    $('.delete-bill').on('click', function() {
        if (!confirm('Are you sure you want to delete this bill?')) {
            return;
        }
        
        const billId = $(this).data('id');
        
        $.ajax({
            url: `/bills/${billId}`,
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
    
    // Mark as Paid
    $('.mark-paid').on('click', function() {
        const billId = $(this).data('bill-id');
        const billName = $(this).data('bill-name');
        const billAmount = $(this).data('bill-amount');
        
        $('#payBillId').val(billId);
        $('#payBillName').text(billName);
        $('#payBillAmount').text(billAmount);
        
        $('#payBillModal').modal('show');
    });
    
    $('#payBillForm').on('submit', function(e) {
        e.preventDefault();
        const billId = $('#payBillId').val();
        
        $.ajax({
            url: `/bills/${billId}/mark-paid`,
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
    
    // Mark as Unpaid
    $('.mark-unpaid').on('click', function() {
        const billId = $(this).data('bill-id');
        
        $.ajax({
            url: `/bills/${billId}/mark-unpaid`,
            method: 'POST',
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
});
</script>
@endsection
