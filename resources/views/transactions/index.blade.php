@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div>
    <!-- Header with integrated summary -->
    <div class="mb-4 lg:mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-3 lg:mb-4">
            <!-- Left side: Title and Add button -->
            <div class="flex justify-between items-center lg:flex-col lg:items-start lg:gap-2">
                <h1 class="text-xl lg:text-2xl font-bold text-gray-800">Transactions</h1>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary lg:self-start">
                    <i class="fas fa-plus"></i>
                    <span class="hidden sm:inline ml-1">Add Transaction</span>
                    <span class="sm:hidden ml-1">Add</span>
                </a>
            </div>
            
            <!-- Right side: Financial Summary -->
            <div class="financial-summary-inline">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-3">
                    <div class="stat-card-inline income">
                        <div class="stat-icon-inline">📈</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-green-600">{{ currency_symbol() }}{{ number_format($totalIncome, 0) }}</div>
                            <div class="stat-label-inline">Income</div>
                        </div>
                    </div>

                    <div class="stat-card-inline expense">
                        <div class="stat-icon-inline">📉</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-red-600">{{ currency_symbol() }}{{ number_format($totalExpense, 0) }}</div>
                            <div class="stat-label-inline">Expenses</div>
                        </div>
                    </div>

                    <div class="stat-card-inline balance">
                        <div class="stat-icon-inline">💰</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline {{ $netAmount >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ currency_symbol() }}{{ number_format($netAmount, 0) }}</div>
                            <div class="stat-label-inline">Net</div>
                        </div>
                    </div>

                    <div class="stat-card-inline">
                        <div class="stat-icon-inline">📊</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline">{{ $transactions->count() }}</div>
                            <div class="stat-label-inline">Count</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card">
            <div class="card-header lg:hidden">
                <button type="button" onclick="toggleFilters()" class="flex items-center justify-between w-full">
                    <span><i class="fas fa-filter"></i> Filters</span>
                    <i class="fas fa-chevron-down" id="filter-icon"></i>
                </button>
            </div>
            <div class="card-body" id="filter-content">
                <form method="GET" class="space-y-3 lg:space-y-4">
                    <!-- Mobile: Search only visible by default -->
                    <div class="lg:hidden">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control" placeholder="Search transactions...">
                    </div>
                    
                    <!-- Full filters for desktop, collapsible for mobile -->
                    <div class="hidden lg:block" id="advanced-filters">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                            <div class="form-group lg:hidden">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       class="form-control" placeholder="Search transactions...">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Date Range</label>
                                <select name="date_range" class="form-select">
                                    <option value="">All Time</option>
                                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
                                    <option value="year" {{ request('date_range') == 'year' ? 'selected' : '' }}>This Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="submit" class="btn btn-primary flex-1">
                            <i class="fas fa-search"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary flex-1 sm:flex-none">
                            <i class="fas fa-times"></i>
                            <span class="hidden sm:inline">Clear</span>
                        </a>
                        <button type="button" onclick="toggleAdvancedFilters()" class="btn btn-secondary lg:hidden">
                            <i class="fas fa-cog"></i>
                            More
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function toggleFilters() {
            const content = document.getElementById('filter-content');
            const icon = document.getElementById('filter-icon');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
        
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advanced-filters');
            
            if (filters.classList.contains('hidden')) {
                filters.classList.remove('hidden');
            } else {
                filters.classList.add('hidden');
            }
        }
        </script>
    </div>

    <!-- Transactions List -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Transaction History
        </div>
        <div class="card-body">
            @if($transactions->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-12"></th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Account</th>
                                    <th>Date</th>
                                    <th class="text-right">Amount</th>
                                    <th class="w-24">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td>
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                                             style="background: {{ $transaction->category->color }}20; color: {{ $transaction->category->color }};">
                                            <i class="fas fa-{{ $transaction->type === 'income' ? 'arrow-up' : 'arrow-down' }} text-xs"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium text-gray-800">
                                            {{ $transaction->description ?: 'No description' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                              style="background: {{ $transaction->category->color }}20; color: {{ $transaction->category->color }};">
                                            {{ $transaction->category->name }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        {{ $transaction->account->name }}
                                    </td>
                                    <td class="text-sm text-gray-500">
                                        {{ $transaction->transaction_date->format('M d, Y') }}
                                    </td>
                                    <td class="text-right">
                                        <div class="font-semibold {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->type === 'income' ? '+' : '' }}{{ currency_symbol() }}{{ number_format(abs($transaction->amount), 2) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-1">
                                            <a href="{{ route('transactions.edit', $transaction) }}" 
                                               class="btn btn-sm btn-secondary p-2" 
                                               title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this transaction?')" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger p-2" title="Delete">
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
                    @foreach($transactions as $transaction)
                    <div class="transaction-item-compact bg-white border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center gap-3">
                            <!-- Icon -->
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" 
                                 style="background: {{ $transaction->category->color }}20; color: {{ $transaction->category->color }};">
                                <i class="fas fa-{{ $transaction->type === 'income' ? 'arrow-up' : 'arrow-down' }} text-xs"></i>
                            </div>
                            
                            <!-- Main content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-gray-800 text-sm truncate">
                                            {{ $transaction->description ?: 'No description' }}
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium" 
                                                  style="background: {{ $transaction->category->color }}20; color: {{ $transaction->category->color }};">
                                                {{ $transaction->category->name }}
                                            </span>
                                            <span class="text-xs text-gray-500">•</span>
                                            <span class="text-xs text-gray-500">{{ $transaction->account->name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{ $transaction->transaction_date->format('M d, Y') }}
                                        </div>
                                    </div>
                                    
                                    <!-- Amount and actions -->
                                    <div class="flex flex-col items-end gap-1 ml-2">
                                        <div class="font-semibold text-sm {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->type === 'income' ? '+' : '' }}{{ currency_symbol() }}{{ number_format(abs($transaction->amount), 0) }}
                                        </div>
                                        
                                        <div class="flex gap-1">
                                            <a href="{{ route('transactions.edit', $transaction) }}" 
                                               class="text-blue-600 hover:text-blue-800 p-1" 
                                               title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this transaction?')" 
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
                
                <!-- Pagination -->
                @if($transactions->hasPages())
                <div class="mt-6">
                    {{ $transactions->links() }}
                </div>
                @endif
            @else
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">📝</div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">No transactions found</h3>
                    <p class="text-gray-600 mb-6">
                        @if(request()->hasAny(['search', 'type', 'category_id', 'date_range']))
                            Try adjusting your filters or search terms.
                        @else
                            Start by adding your first transaction!
                        @endif
                    </p>
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i>
                        Add Transaction
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection