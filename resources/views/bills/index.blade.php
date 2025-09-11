@extends('layouts.app')

@section('title', 'Bills & Subscriptions')

@section('content')
<div>
    <!-- Header with integrated summary -->
    <div class="mb-4 lg:mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-3 lg:mb-4">
            <!-- Left side: Title and Add button -->
            <div class="flex justify-between items-center lg:flex-col lg:items-start lg:gap-2">
                <h1 class="text-xl lg:text-2xl font-bold text-gray-800">Bills & Subscriptions</h1>
                <button onclick="showAddBillModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="hidden sm:inline ml-1">Add Bill</span>
                    <span class="sm:hidden ml-1">Add</span>
                </button>
            </div>
            
            <!-- Right side: Financial Summary -->
            <div class="financial-summary-inline">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-3">
                    <div class="stat-card-inline expense">
                        <div class="stat-icon-inline">⚠️</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-red-600">{{ $overdueBills->count() }}</div>
                            <div class="stat-label-inline">Overdue</div>
                        </div>
                    </div>

                    <div class="stat-card-inline balance">
                        <div class="stat-icon-inline">📅</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-orange-600">{{ $upcomingBills->count() }}</div>
                            <div class="stat-label-inline">Due Soon</div>
                        </div>
                    </div>

                    <div class="stat-card-inline income">
                        <div class="stat-icon-inline">✅</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-green-600">{{ $paidBills->count() }}</div>
                            <div class="stat-label-inline">Paid</div>
                        </div>
                    </div>

                    <div class="stat-card-inline">
                        <div class="stat-icon-inline">🔄</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-blue-600">{{ $recurringBills->count() }}</div>
                            <div class="stat-label-inline">Recurring</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Bills -->
    <div class="card mb-4 lg:mb-6">
        <div class="card-header">
            <i class="fas fa-list"></i> All Bills
            <span class="text-sm text-gray-500 ml-2">(Sorted by due date)</span>
        </div>
        <div class="card-body p-3 lg:p-4">
            @if($allBills->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-12"></th>
                                    <th>Bill Name</th>
                                    <th>Category</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="text-right">Amount</th>
                                    <th class="w-24">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allBills as $bill)
                                <tr class="hover:bg-gray-50 {{ $bill->is_paid ? 'bg-green-50' : ($bill->due_date->isPast() ? 'bg-red-50' : ($bill->due_date->isToday() ? 'bg-orange-50' : '')) }}">
                                    <td>
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $bill->is_paid ? 'bg-green-100 text-green-600' : ($bill->due_date->isPast() ? 'bg-red-100 text-red-600' : ($bill->due_date->isToday() ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600')) }}">
                                            @if($bill->is_paid)
                                                <i class="fas fa-check text-xs"></i>
                                            @elseif($bill->due_date->isPast())
                                                <i class="fas fa-exclamation-triangle text-xs"></i>
                                            @elseif($bill->due_date->isToday())
                                                <i class="fas fa-clock text-xs"></i>
                                            @else
                                                <i class="fas fa-file-invoice text-xs"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium text-gray-800 text-sm">
                                            {{ $bill->name }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-sm text-gray-600">{{ $bill->category->name }}</span>
                                    </td>
                                    <td class="text-sm text-gray-500">
                                        {{ $bill->due_date->format('M d, Y') }}
                                    </td>
                                    <td>
                                        @if($bill->is_paid)
                                            <span class="badge badge-success">Paid</span>
                                        @elseif($bill->due_date->isPast())
                                            <span class="badge badge-danger">Overdue</span>
                                        @elseif($bill->due_date->isToday())
                                            <span class="badge badge-warning">Due Today</span>
                                        @else
                                            <span class="badge badge-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="font-semibold text-sm text-gray-800">
                                            {{ currency_symbol() }}{{ number_format($bill->amount, 0) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-1">
                                            @if(!$bill->is_paid)
                                                <form method="POST" action="{{ route('bills.markPaid', $bill) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-800 p-1" title="Mark Paid">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('bills.markUnpaid', $bill) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-orange-600 hover:text-orange-800 p-1" title="Mark Unpaid">
                                                        <i class="fas fa-undo text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button onclick="editBill({{ $bill->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <form method="POST" action="{{ route('bills.destroy', $bill) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this bill?')" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
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
                    @foreach($allBills as $bill)
                    <div class="bill-item-compact {{ $bill->is_paid ? 'bg-green-50 border-green-200' : ($bill->due_date->isPast() ? 'bg-red-50 border-red-200' : ($bill->due_date->isToday() ? 'bg-orange-50 border-orange-200' : 'bg-white border-gray-200')) }} border rounded-lg p-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $bill->is_paid ? 'bg-green-100 text-green-600' : ($bill->due_date->isPast() ? 'bg-red-100 text-red-600' : ($bill->due_date->isToday() ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600')) }}">
                                @if($bill->is_paid)
                                    <i class="fas fa-check text-xs"></i>
                                @elseif($bill->due_date->isPast())
                                    <i class="fas fa-exclamation-triangle text-xs"></i>
                                @elseif($bill->due_date->isToday())
                                    <i class="fas fa-clock text-xs"></i>
                                @else
                                    <i class="fas fa-file-invoice text-xs"></i>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 text-sm truncate">{{ $bill->name }}</div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-600">{{ $bill->category->name }}</span>
                                            <span class="text-xs text-gray-400">•</span>
                                            <span class="text-xs text-gray-500">{{ $bill->due_date->format('M d, Y') }}</span>
                                            @if($bill->is_recurring)
                                                <span class="text-xs text-blue-600">• Recurring</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            @if($bill->is_paid)
                                                <span class="badge badge-success text-xs">Paid</span>
                                                @if($bill->paid_at)
                                                    <span class="text-xs text-gray-500">on {{ $bill->paid_at->format('M d') }}</span>
                                                @endif
                                            @elseif($bill->due_date->isPast())
                                                <span class="badge badge-danger text-xs">Overdue</span>
                                                <span class="text-xs text-red-600">{{ $bill->due_date->diffForHumans() }}</span>
                                            @elseif($bill->due_date->isToday())
                                                <span class="badge badge-warning text-xs">Due Today!</span>
                                            @elseif($bill->due_date->isTomorrow())
                                                <span class="badge badge-warning text-xs">Due Tomorrow</span>
                                            @else
                                                <span class="badge badge-secondary text-xs">Pending</span>
                                                <span class="text-xs text-gray-500">in {{ $bill->due_date->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col items-end gap-1 ml-2">
                                        <div class="font-semibold text-sm text-gray-800">
                                            {{ currency_symbol() }}{{ number_format($bill->amount, 0) }}
                                        </div>
                                        
                                        <div class="flex gap-1">
                                            @if(!$bill->is_paid)
                                                <form method="POST" action="{{ route('bills.markPaid', $bill) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-800 p-1" title="Mark Paid">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('bills.markUnpaid', $bill) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-orange-600 hover:text-orange-800 p-1" title="Mark Unpaid">
                                                        <i class="fas fa-undo text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button onclick="editBill({{ $bill->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <form method="POST" action="{{ route('bills.destroy', $bill) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this bill?')" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 lg:py-12">
                    <div class="text-4xl lg:text-6xl mb-3 lg:mb-4">📄</div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800 mb-2">No bills found</h3>
                    <p class="text-sm lg:text-base text-gray-600 mb-4 lg:mb-6">Add your first bill to start tracking your expenses!</p>
                    <button onclick="showAddBillModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Bill
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Bill Modal -->
<div id="addBillModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Add Bill</h3>
                <button onclick="hideAddBillModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('bills.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Bill Name</label>
                    <input type="text" name="name" class="form-control" 
                           placeholder="e.g., Electricity Bill" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" 
                           value="{{ now()->addDays(7)->format('Y-m-d') }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="is_recurring" value="1" class="mr-2">
                        Recurring Bill
                    </label>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideAddBillModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Add Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Bill Modal -->
<div id="editBillModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Bill</h3>
                <button onclick="hideEditBillModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" id="editBillForm">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Bill Name</label>
                    <input type="text" name="name" id="editBillName" class="form-control" 
                           placeholder="e.g., Electricity Bill" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" id="editBillAmount" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" id="editBillDueDate" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="editBillCategory" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Frequency</label>
                    <select name="frequency" id="editBillFrequency" class="form-select">
                        <option value="once">One-time</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="is_recurring" id="editBillRecurring" value="1" class="mr-2">
                        Recurring Bill
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" id="editBillNotes" class="form-control" rows="3" 
                              placeholder="Add any additional notes about this bill"></textarea>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideEditBillModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Update Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    function showAddBillModal() {
        document.getElementById('addBillModal').classList.remove('hidden');
    }
    
    function hideAddBillModal() {
        document.getElementById('addBillModal').classList.add('hidden');
    }
    
    function showEditBillModal() {
        document.getElementById('editBillModal').classList.remove('hidden');
    }
    
    function hideEditBillModal() {
        document.getElementById('editBillModal').classList.add('hidden');
    }
    
    function editBill(billId) {
        // Redirect to the edit page
        window.location.href = `/bills/${billId}/edit`;
    }
    
    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        const addModal = document.getElementById('addBillModal');
        const editModal = document.getElementById('editBillModal');
        
        if (event.target === addModal) {
            hideAddBillModal();
        }
        if (event.target === editModal) {
            hideEditBillModal();
        }
    });
    
    // Handle edit form submission
    document.getElementById('editBillForm').addEventListener('submit', function(e) {
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
                // Reload the page to show updated data
                window.location.reload();
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
</script>
@endsection