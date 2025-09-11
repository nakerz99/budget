@extends('layouts.app')

@section('title', 'Budget')

@section('content')
<div>
    <!-- Header with integrated summary -->
    <div class="mb-4 lg:mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-3 lg:mb-4">
            <!-- Left side: Title and buttons -->
            <div class="flex-1">
                <h1 class="text-xl lg:text-2xl font-bold text-gray-800 mb-2">Monthly Budget</h1>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button onclick="copyFromLastMonth()" class="btn btn-secondary btn-sm">
                        <i class="fas fa-copy"></i>
                        <span class="hidden sm:inline ml-1">Copy Last Month</span>
                        <span class="sm:hidden ml-1">Copy</span>
                    </button>
                    <button onclick="showAddBudgetModal()" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline ml-1">Add Budget</span>
                        <span class="sm:hidden ml-1">Add</span>
                    </button>
                </div>
            </div>
            
            <!-- Right side: Financial Summary -->
            <div class="financial-summary-inline">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-3">
                    <div class="stat-card-inline income">
                        <div class="stat-icon-inline">📊</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-blue-600">{{ currency_symbol() }}{{ number_format($summary['total_budget'], 0) }}</div>
                            <div class="stat-label-inline">Budget</div>
                        </div>
                    </div>

                    <div class="stat-card-inline expense">
                        <div class="stat-icon-inline">💸</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-red-600">{{ currency_symbol() }}{{ number_format($summary['total_spent'], 0) }}</div>
                            <div class="stat-label-inline">Spent</div>
                        </div>
                    </div>

                    <div class="stat-card-inline balance">
                        <div class="stat-icon-inline">💰</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline {{ $summary['total_remaining'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ currency_symbol() }}{{ number_format($summary['total_remaining'], 0) }}</div>
                            <div class="stat-label-inline">Remaining</div>
                        </div>
                    </div>

                    <div class="stat-card-inline">
                        <div class="stat-icon-inline">📈</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline {{ $summary['percentage_used'] > 100 ? 'text-red-600' : ($summary['percentage_used'] > 80 ? 'text-orange-600' : 'text-green-600') }}">{{ $summary['percentage_used'] }}%</div>
                            <div class="stat-label-inline">Used</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Month Selector -->
        <div class="card">
            <div class="card-body p-3 lg:p-4">
                <form method="GET" class="flex gap-4 items-end">
                    <div class="form-group flex-1">
                        <label class="form-label">Select Month</label>
                        <select name="month" class="form-select" onchange="this.form.submit()">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 1)) }} {{ date('Y') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Budget Categories -->
    <div class="card mb-4 lg:mb-6">
        <div class="card-header">
            <i class="fas fa-chart-pie"></i> Budget by Category
        </div>
        <div class="card-body p-3 lg:p-4">
            @if(count($budgetData) > 0)
                <div class="space-y-3">
                    @foreach($budgetData as $budgetItem)
                    <div class="budget-item-compact">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                                     style="background: {{ $budgetItem['category']->color }}20; color: {{ $budgetItem['category']->color }};">
                                    <i class="fas fa-tag text-xs"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800 text-sm">{{ $budgetItem['category']->name }}</div>
                                    <div class="text-xs text-gray-600">
                                        {{ currency_symbol() }}{{ number_format($budgetItem['budget_amount'], 0) }} budget
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-sm {{ $budgetItem['spent'] > $budgetItem['budget_amount'] ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ currency_symbol() }}{{ number_format($budgetItem['spent'], 0) }}
                                </div>
                                <div class="text-xs text-gray-600">spent</div>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                            @php
                                $percentage = $budgetItem['budget_amount'] > 0 ? min(100, ($budgetItem['spent'] / $budgetItem['budget_amount']) * 100) : 0;
                                $barColor = $percentage > 100 ? '#ef4444' : ($percentage > 80 ? '#f59e0b' : '#3b82f6');
                            @endphp
                            <div class="h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $percentage }}%; background: {{ $barColor }};"></div>
                        </div>
                        
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-600">
                                {{ number_format($percentage, 1) }}% used
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-600">
                                    {{ currency_symbol() }}{{ number_format($budgetItem['budget_amount'] - $budgetItem['spent'], 0) }} remaining
                                </span>
                                @if($budgetItem['budget'])
                                <div class="flex gap-1">
                                    <button onclick="editBudget({{ $budgetItem['budget']->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Edit Budget">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <form method="POST" action="{{ route('budget.destroy', $budgetItem['budget']->id) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete this budget?')" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Delete Budget">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 lg:py-12">
                    <div class="text-4xl lg:text-6xl mb-3 lg:mb-4">📊</div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800 mb-2">No budget set for this month</h3>
                    <p class="text-sm lg:text-base text-gray-600 mb-4 lg:mb-6">Create your first budget to start tracking your spending!</p>
                    <button onclick="showAddBudgetModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Create Budget
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Budget vs Actual - Split into 2 cards -->
    @if($budgets->count() > 0)
    <div class="budget-vs-actual-grid">
        @php
            $budgetsArray = $budgets->toArray();
            $halfCount = ceil(count($budgetsArray) / 2);
            $firstHalf = array_slice($budgetsArray, 0, $halfCount);
            $secondHalf = array_slice($budgetsArray, $halfCount);
        @endphp
        
        <!-- First Card -->
        <div class="card mb-6">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Budget Overview (Part 1)
            </div>
            <div class="card-body">
                <div class="budget-comparison-grid">
                    @foreach($firstHalf as $budget)
                    <div class="budget-comparison-card">
                        <!-- Category Header -->
                        <div class="budget-category-header">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" 
                                     style="background: {{ $budget['category']['color'] }}20; color: {{ $budget['category']['color'] }};">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">{{ $budget['category']['name'] }}</h3>
                                    <div class="text-sm text-gray-500">
                                        @php
                                            $percentage = $budget['amount'] > 0 ? min(100, ($budget['spent'] / $budget['amount']) * 100) : 0;
                                        @endphp
                                        {{ number_format($percentage, 1) }}% used
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="budget-progress-container">
                            @php
                                $barColor = $percentage > 100 ? '#ef4444' : ($percentage > 80 ? '#f59e0b' : '#3b82f6');
                            @endphp
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-300" 
                                     style="width: {{ $percentage }}%; background: {{ $barColor }};"></div>
                            </div>
                        </div>

                        <!-- Values Grid -->
                        <div class="budget-values-container">
                            <div class="budget-value-group">
                                <div class="budget-value-label">Budget</div>
                                <div class="budget-value-amount">{{ currency_symbol() }}{{ number_format($budget['amount'], 0) }}</div>
                            </div>
                            
                            <div class="budget-value-group">
                                <div class="budget-value-label">Spent</div>
                                <div class="budget-value-amount {{ $budget['spent'] > $budget['amount'] ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ currency_symbol() }}{{ number_format($budget['spent'], 0) }}
                                </div>
                            </div>
                            
                            <div class="budget-value-group">
                                <div class="budget-value-label">Remaining</div>
                                <div class="budget-value-amount {{ ($budget['amount'] - $budget['spent']) < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ ($budget['amount'] - $budget['spent']) < 0 ? '-' : '' }}{{ currency_symbol() }}{{ number_format(abs($budget['amount'] - $budget['spent']), 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Second Card -->
        @if(count($secondHalf) > 0)
        <div class="card mb-6">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Budget Overview (Part 2)
            </div>
            <div class="card-body">
                <div class="budget-comparison-grid">
                    @foreach($secondHalf as $budget)
                    <div class="budget-comparison-card">
                        <!-- Category Header -->
                        <div class="budget-category-header">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" 
                                     style="background: {{ $budget['category']['color'] }}20; color: {{ $budget['category']['color'] }};">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">{{ $budget['category']['name'] }}</h3>
                                    <div class="text-sm text-gray-500">
                                        @php
                                            $percentage = $budget['amount'] > 0 ? min(100, ($budget['spent'] / $budget['amount']) * 100) : 0;
                                        @endphp
                                        {{ number_format($percentage, 1) }}% used
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="budget-progress-container">
                            @php
                                $barColor = $percentage > 100 ? '#ef4444' : ($percentage > 80 ? '#f59e0b' : '#3b82f6');
                            @endphp
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-300" 
                                     style="width: {{ $percentage }}%; background: {{ $barColor }};"></div>
                            </div>
                        </div>

                        <!-- Values Grid -->
                        <div class="budget-values-container">
                            <div class="budget-value-group">
                                <div class="budget-value-label">Budget</div>
                                <div class="budget-value-amount">{{ currency_symbol() }}{{ number_format($budget['amount'], 0) }}</div>
                            </div>
                            
                            <div class="budget-value-group">
                                <div class="budget-value-label">Spent</div>
                                <div class="budget-value-amount {{ $budget['spent'] > $budget['amount'] ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ currency_symbol() }}{{ number_format($budget['spent'], 0) }}
                                </div>
                            </div>
                            
                            <div class="budget-value-group">
                                <div class="budget-value-label">Remaining</div>
                                <div class="budget-value-amount {{ ($budget['amount'] - $budget['spent']) < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ ($budget['amount'] - $budget['spent']) < 0 ? '-' : '' }}{{ currency_symbol() }}{{ number_format(abs($budget['amount'] - $budget['spent']), 0) }}
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
    @endif
</div>

<!-- Add Budget Modal -->
<div id="addBudgetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Add Budget</h3>
                <button onclick="hideAddBudgetModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('budget.store') }}">
                @csrf
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
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideAddBudgetModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Add Budget
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Budget Modal -->
<div id="editBudgetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Budget</h3>
                <button onclick="hideEditBudgetModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" id="editBudgetForm">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="editCategoryId" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" id="editAmount" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideEditBudgetModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Update Budget
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    function showAddBudgetModal() {
        document.getElementById('addBudgetModal').classList.remove('hidden');
    }
    
    function hideAddBudgetModal() {
        document.getElementById('addBudgetModal').classList.add('hidden');
    }
    
    function showEditBudgetModal() {
        document.getElementById('editBudgetModal').classList.remove('hidden');
    }
    
    function hideEditBudgetModal() {
        document.getElementById('editBudgetModal').classList.add('hidden');
    }
    
    function editBudget(budgetId) {
        // Fetch budget data and populate the edit form
        fetch(`/budget/${budgetId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editCategoryId').value = data.category_id;
                document.getElementById('editAmount').value = data.amount;
                document.getElementById('editBudgetForm').action = `/budget/${budgetId}`;
                showEditBudgetModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading budget data');
            });
    }
    
    function copyFromLastMonth() {
        if (confirm('Copy budget from last month?')) {
            window.location.href = '{{ route("budget.copy", ["month" => date("n") - 1, "year" => date("Y")]) }}';
        }
    }
    
    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        const addModal = document.getElementById('addBudgetModal');
        const editModal = document.getElementById('editBudgetModal');
        
        if (event.target === addModal) {
            hideAddBudgetModal();
        }
        if (event.target === editModal) {
            hideEditBudgetModal();
        }
    });
</script>
@endsection