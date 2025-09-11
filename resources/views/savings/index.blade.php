@extends('layouts.app')

@section('title', 'Savings Goals')

@section('content')
<div>
    <!-- Header with integrated summary -->
    <div class="mb-4 lg:mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-3 lg:mb-4">
            <!-- Left side: Title and Add button -->
            <div class="flex justify-between items-center lg:flex-col lg:items-start lg:gap-2">
                <h1 class="text-xl lg:text-2xl font-bold text-gray-800">Savings Goals</h1>
                <button onclick="showAddGoalModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="hidden sm:inline ml-1">Add Goal</span>
                    <span class="sm:hidden ml-1">Add</span>
                </button>
            </div>
            
            <!-- Right side: Financial Summary -->
            <div class="financial-summary-inline">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-3">
                    <div class="stat-card-inline savings">
                        <div class="stat-icon-inline">🎯</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-purple-600">{{ $savingsGoals->count() }}</div>
                            <div class="stat-label-inline">Active</div>
                        </div>
                    </div>

                    <div class="stat-card-inline balance">
                        <div class="stat-icon-inline">💰</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-green-600">{{ currency_symbol() }}{{ number_format($totalSaved, 0) }}</div>
                            <div class="stat-label-inline">Saved</div>
                        </div>
                    </div>

                    <div class="stat-card-inline income">
                        <div class="stat-icon-inline">📈</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-blue-600">{{ currency_symbol() }}{{ number_format($totalTarget, 0) }}</div>
                            <div class="stat-label-inline">Target</div>
                        </div>
                    </div>

                    <div class="stat-card-inline">
                        <div class="stat-icon-inline">✅</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-orange-600">{{ $completedGoals->count() }}</div>
                            <div class="stat-label-inline">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Savings Goals -->
    <div class="card mb-4 lg:mb-6">
        <div class="card-header">
            <i class="fas fa-piggy-bank"></i> Active Goals
        </div>
        <div class="card-body p-3 lg:p-4">
            @if($savingsGoals->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-12"></th>
                                    <th>Goal Name</th>
                                    <th>Target Amount</th>
                                    <th>Current Amount</th>
                                    <th>Progress</th>
                                    <th>Due Date</th>
                                    <th class="w-24">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($savingsGoals as $goal)
                                <tr class="hover:bg-gray-50">
                                    <td>
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-{{ $goal->icon ?: 'piggy-bank' }} text-blue-600 text-xs"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium text-gray-800 text-sm">
                                            {{ $goal->name }}
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        {{ currency_symbol() }}{{ number_format($goal->target_amount, 0) }}
                                    </td>
                                    <td class="text-sm text-gray-800 font-semibold">
                                        {{ currency_symbol() }}{{ number_format($goal->current_amount, 0) }}
                                    </td>
                                    <td>
                                        @php
                                            $percentage = min(100, ($goal->current_amount / $goal->target_amount) * 100);
                                            $barColor = $percentage >= 100 ? '#10b981' : ($percentage >= 75 ? '#3b82f6' : '#f59e0b');
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                                <div class="h-2 rounded-full transition-all duration-500" 
                                                     style="width: {{ $percentage }}%; background: {{ $barColor }};"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ number_format($percentage, 1) }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-500">
                                        @if($goal->target_date)
                                            {{ $goal->target_date->format('M d, Y') }}
                                        @else
                                            No date
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex gap-1">
                                            <button onclick="addContribution({{ $goal->id }})" class="text-green-600 hover:text-green-800 p-1" title="Add Money">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                            <button onclick="editGoal({{ $goal->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <form method="POST" action="{{ route('savings.destroyGoal', $goal) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this goal?')" 
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
                    @foreach($savingsGoals as $goal)
                    <div class="savings-goal-item-compact bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-{{ $goal->icon ?: 'piggy-bank' }} text-blue-600 text-xs"></i>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 text-sm truncate">{{ $goal->name }}</div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-600">Target: {{ currency_symbol() }}{{ number_format($goal->target_amount, 0) }}</span>
                                            @if($goal->target_date)
                                                <span class="text-xs text-gray-400">•</span>
                                                <span class="text-xs text-gray-500">{{ $goal->target_date->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                                @php
                                                    $percentage = min(100, ($goal->current_amount / $goal->target_amount) * 100);
                                                    $barColor = $percentage >= 100 ? '#10b981' : ($percentage >= 75 ? '#3b82f6' : '#f59e0b');
                                                @endphp
                                                <div class="h-1.5 rounded-full transition-all duration-500" 
                                                     style="width: {{ $percentage }}%; background: {{ $barColor }};"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ number_format($percentage, 1) }}%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col items-end gap-1 ml-2">
                                        <div class="font-semibold text-sm text-gray-800">
                                            {{ currency_symbol() }}{{ number_format($goal->current_amount, 0) }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            {{ currency_symbol() }}{{ number_format($goal->target_amount - $goal->current_amount, 0) }} left
                                        </div>
                                        
                                        <div class="flex gap-1">
                                            <button onclick="addContribution({{ $goal->id }})" class="text-green-600 hover:text-green-800 p-1" title="Add Money">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                            <button onclick="editGoal({{ $goal->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <form method="POST" action="{{ route('savings.destroyGoal', $goal) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this goal?')" 
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
                    <div class="text-4xl lg:text-6xl mb-3 lg:mb-4">🎯</div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800 mb-2">No savings goals yet</h3>
                    <p class="text-sm lg:text-base text-gray-600 mb-4 lg:mb-6">Create your first savings goal to start building your financial future!</p>
                    <button onclick="showAddGoalModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Create Goal
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Completed Goals -->
    @if($completedGoals->count() > 0)
    <div class="card mb-6">
        <div class="card-header">
            <i class="fas fa-trophy"></i> Completed Goals
        </div>
        <div class="card-body">
            <div class="space-y-3">
                @foreach($completedGoals as $goal)
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-trophy text-green-600"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">{{ $goal->name }}</div>
                            <div class="text-sm text-gray-600">
                                Completed {{ $goal->updated_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-green-600">
                            {{ currency_symbol() }}{{ number_format($goal->target_amount, 0) }}
                        </div>
                        <div class="text-sm text-gray-600">Achieved!</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Savings Accounts -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-university"></i> Savings Accounts
            <span class="text-sm text-gray-500 ml-2">(Your real bank accounts)</span>
        </div>
        <div class="card-body">
            @if($savingsAccounts->count() > 0)
                <div class="space-y-3">
                    @foreach($savingsAccounts as $account)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-university text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800">{{ $account->name }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ $account->bank_name ?? 'Bank' }} • {{ ucfirst(str_replace('_', ' ', $account->account_type)) }}
                                    @if($account->account_number)
                                        • {{ $account->account_number }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <div class="font-bold text-lg {{ $account->balance >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                                    {{ currency_symbol() }}{{ number_format($account->balance, 0) }}
                                </div>
                                <div class="text-sm text-gray-600">Current Balance</div>
                            </div>
                            <div class="flex gap-1">
                                <button onclick="editAccount({{ $account->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Edit Account">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <form method="POST" action="{{ route('savings.destroyAccount', $account) }}" 
                                      onsubmit="return confirm('Are you sure you want to delete this savings account? This will also delete all associated savings goals.')" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Delete Account">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-4xl mb-4">🏦</div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">No savings accounts</h3>
                    <p class="text-gray-600 mb-4">Add your real bank accounts to track your savings!</p>
                    <button onclick="showAddAccountModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Bank Account
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Goal Modal -->
<div id="addGoalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Create Savings Goal</h3>
                <button onclick="hideAddGoalModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('savings.storeGoal') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Goal Name</label>
                    <input type="text" name="name" class="form-control" 
                           placeholder="e.g., Emergency Fund" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Target Amount</label>
                    <input type="number" name="target_amount" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Savings Account</label>
                    <select name="account_id" class="form-select" required>
                        <option value="">Select savings account</option>
                        @foreach($savingsAccounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->name }} ({{ $account->bank_name ?? 'Bank' }}) - {{ currency_symbol() }}{{ number_format($account->balance, 0) }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-gray-500">Choose which bank account to save money in</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Current Amount</label>
                    <input type="number" name="current_amount" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" value="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Target Date (Optional)</label>
                    <input type="date" name="target_date" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Describe your savings goal..."></textarea>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideAddGoalModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Create Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Contribution Modal -->
<div id="addContributionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Add Contribution</h3>
                <button onclick="hideAddContributionModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('savings.addContribution') }}">
                @csrf
                <input type="hidden" name="goal_id" id="contributionGoalId">
                
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control" 
                           placeholder="0.00" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Transfer From Account</label>
                    <select name="from_account_id" class="form-select" required>
                        <option value="">Select source account</option>
                        @foreach($allAccounts as $account)
                            @if($account->type !== 'savings')
                                <option value="{{ $account->id }}" data-balance="{{ $account->balance }}">
                                    {{ $account->name }} - {{ currency_symbol() }}{{ number_format($account->balance, 0) }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <small class="text-gray-500">Choose which account to transfer money from</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <input type="text" name="description" class="form-control" 
                           placeholder="e.g., Monthly contribution">
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideAddContributionModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success flex-1">
                        Add Contribution
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Account Modal -->
<div id="addAccountModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Add Savings Account</h3>
                <button onclick="hideAddAccountModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('savings.storeAccount') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" 
                           placeholder="e.g., BPI, BDO, Metrobank" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Account Name</label>
                    <input type="text" name="name" class="form-control" 
                           placeholder="e.g., BPI Savings, BDO Time Deposit" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Account Type</label>
                    <select name="account_type" class="form-select" required>
                        <option value="savings">Savings Account</option>
                        <option value="time_deposit">Time Deposit</option>
                        <option value="money_market">Money Market</option>
                        <option value="cd">Certificate of Deposit</option>
                        <option value="investment">Investment Account</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Account Number (Optional)</label>
                    <input type="text" name="account_number" class="form-control" 
                           placeholder="e.g., 1234-5678-9012">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Current Balance</label>
                    <input type="number" name="balance" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" value="0" required>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideAddAccountModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Add Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Account Modal -->
<div id="editAccountModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Savings Account</h3>
                <button onclick="hideEditAccountModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" id="editAccountForm">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" id="editBankName" class="form-control" 
                           placeholder="e.g., BPI, BDO, Metrobank" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Account Name</label>
                    <input type="text" name="name" id="editAccountName" class="form-control" 
                           placeholder="e.g., BPI Savings, BDO Time Deposit" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Account Type</label>
                    <select name="account_type" id="editAccountType" class="form-select" required>
                        <option value="savings">Savings Account</option>
                        <option value="time_deposit">Time Deposit</option>
                        <option value="money_market">Money Market</option>
                        <option value="cd">Certificate of Deposit</option>
                        <option value="investment">Investment Account</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Account Number (Optional)</label>
                    <input type="text" name="account_number" id="editAccountNumber" class="form-control" 
                           placeholder="e.g., 1234-5678-9012">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Current Balance</label>
                    <input type="number" name="balance" id="editAccountBalance" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideEditAccountModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Update Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Goal Modal -->
<div id="editGoalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Savings Goal</h3>
                <button onclick="hideEditGoalModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" id="editGoalForm">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Goal Name</label>
                    <input type="text" name="name" id="editGoalName" class="form-control" 
                           placeholder="e.g., Emergency Fund" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Target Amount</label>
                    <input type="number" name="target_amount" id="editTargetAmount" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Current Amount</label>
                    <input type="number" name="current_amount" id="editCurrentAmount" class="form-control" 
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Target Date (Optional)</label>
                    <input type="date" name="target_date" id="editTargetDate" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea name="description" id="editDescription" class="form-control" rows="3" 
                              placeholder="Describe your savings goal..."></textarea>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideEditGoalModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Update Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    function showAddGoalModal() {
        document.getElementById('addGoalModal').classList.remove('hidden');
    }
    
    function hideAddGoalModal() {
        document.getElementById('addGoalModal').classList.add('hidden');
    }
    
    function showAddAccountModal() {
        document.getElementById('addAccountModal').classList.remove('hidden');
    }
    
    function hideAddAccountModal() {
        document.getElementById('addAccountModal').classList.add('hidden');
    }
    
    function addContribution(goalId) {
        document.getElementById('contributionGoalId').value = goalId;
        document.getElementById('addContributionModal').classList.remove('hidden');
    }
    
    function hideAddContributionModal() {
        document.getElementById('addContributionModal').classList.add('hidden');
    }
    
    function showEditGoalModal() {
        document.getElementById('editGoalModal').classList.remove('hidden');
    }
    
    function hideEditGoalModal() {
        document.getElementById('editGoalModal').classList.add('hidden');
    }
    
    function showEditAccountModal() {
        document.getElementById('editAccountModal').classList.remove('hidden');
    }
    
    function hideEditAccountModal() {
        document.getElementById('editAccountModal').classList.add('hidden');
    }
    
    function editAccount(accountId) {
        // Fetch account data and populate the edit form
        fetch(`/savings/accounts/${accountId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editBankName').value = data.bank_name || '';
                document.getElementById('editAccountName').value = data.name;
                document.getElementById('editAccountType').value = data.account_type;
                document.getElementById('editAccountNumber').value = data.account_number || '';
                document.getElementById('editAccountBalance').value = data.balance;
                document.getElementById('editAccountForm').action = `/savings/accounts/${accountId}`;
                showEditAccountModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading account data');
            });
    }
    
    function editGoal(goalId) {
        // Fetch goal data and populate the edit form
        fetch(`/savings/goals/${goalId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editGoalName').value = data.name;
                document.getElementById('editTargetAmount').value = data.target_amount;
                document.getElementById('editCurrentAmount').value = data.current_amount;
                document.getElementById('editTargetDate').value = data.target_date;
                document.getElementById('editDescription').value = data.description || '';
                document.getElementById('editGoalForm').action = `/savings/goals/${goalId}`;
                showEditGoalModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading goal data');
            });
    }
    
    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        const modals = ['addGoalModal', 'addContributionModal', 'addAccountModal', 'editAccountModal', 'editGoalModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
    
    // Handle edit account form submission
    document.getElementById('editAccountForm').addEventListener('submit', function(e) {
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
                alert('Account updated successfully!');
                // Reload the page to show updated data
                window.location.reload();
            } else {
                // Show error message
                alert('Error: ' + (data.message || 'Failed to update account'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the account');
        });
    });
</script>
@endsection