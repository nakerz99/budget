@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Savings Goals</h1>
                <div>
                    <button class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                        <i class="fas fa-piggy-bank"></i> Add Savings Account
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                        <i class="fas fa-plus"></i> New Goal
                    </button>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Total Savings</div>
                            <div class="h4 mb-0 text-success">{{ currency_symbol() }}{{ number_format($totalSavings, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Goals Progress</div>
                            <div class="h4 mb-0">{{ currency_symbol() }}{{ number_format($totalGoalCurrent, 2) }} / {{ currency_symbol() }}{{ number_format($totalGoalTarget, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Active Goals</div>
                            <div class="h4 mb-0">{{ $activeGoalsCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Monthly Savings</div>
                            <div class="h4 mb-0 text-info">{{ currency_symbol() }}{{ number_format($monthlySavingsRate, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Savings Goals -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">Active Savings Goals</h5>
            <div class="row g-3">
                @forelse($savingsGoals->where('is_completed', false) as $goal)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">{{ $goal->name }}</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item contribute-btn" href="#" 
                                               data-goal-id="{{ $goal->id }}"
                                               data-goal-name="{{ $goal->name }}">
                                                <i class="fas fa-plus-circle"></i> Add Contribution
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item edit-goal" href="#" 
                                               data-goal='@json($goal)'
                                               data-bs-toggle="modal" 
                                               data-bs-target="#editGoalModal">
                                                <i class="fas fa-edit"></i> Edit Goal
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger delete-goal" href="#" 
                                               data-id="{{ $goal->id }}">
                                                <i class="fas fa-trash"></i> Delete Goal
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Progress</span>
                                    <span class="fw-bold">{{ $goal->progress_percentage }}%</span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         style="width: {{ $goal->progress_percentage }}%; background-color: {{ $goal->color }};"
                                         aria-valuenow="{{ $goal->progress_percentage }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="text-muted small">Current</div>
                                    <div class="fw-bold">{{ currency_symbol() }}{{ number_format($goal->current_amount, 2) }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Target</div>
                                    <div class="fw-bold">{{ currency_symbol() }}{{ number_format($goal->target_amount, 2) }}</div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="text-muted small">Days Left</div>
                                    <div class="fw-bold {{ $goal->days_remaining < 30 ? 'text-warning' : '' }}">
                                        {{ max(0, $goal->days_remaining) }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Monthly Need</div>
                                    <div class="fw-bold">{{ currency_symbol() }}{{ number_format($goal->monthly_required, 2) }}</div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-piggy-bank"></i> {{ $goal->account->name }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No active savings goals. Create your first goal to start saving!</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                            Create Savings Goal
                        </button>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Completed Goals -->
    @if($savingsGoals->where('is_completed', true)->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">Completed Goals 🎉</h5>
            <div class="row g-3">
                @foreach($savingsGoals->where('is_completed', true) as $goal)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 bg-light">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $goal->name }} 
                                <span class="badge bg-success">Completed</span>
                            </h5>
                            <p class="mb-0">
                                <strong>Saved:</strong> {{ currency_symbol() }}{{ number_format($goal->current_amount, 2) }}<br>
                                <strong>Completed:</strong> {{ $goal->updated_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Savings Accounts -->
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3">Savings Accounts</h5>
            <div class="row g-3">
                @forelse($savingsAccounts as $account)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">{{ $account->name }}</h5>
                            <h3 class="text-success">{{ currency_symbol() }}{{ number_format($account->balance, 2) }}</h3>
                            <small class="text-muted">
                                {{ $savingsGoals->where('account_id', $account->id)->count() }} active goal(s)
                            </small>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-4">
                        <p class="text-muted">No savings accounts yet. Create one to start tracking your savings!</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addGoalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Savings Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Goal Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="target_amount" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target Date</label>
                        <input type="date" class="form-control" name="target_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Savings Account</label>
                        <select class="form-select" name="account_id" required>
                            <option value="">Select Account</option>
                            @foreach($savingsAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" name="color" value="#8B5CF6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Goal Modal -->
<div class="modal fade" id="editGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editGoalForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="goal_id" id="editGoalId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Savings Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Goal Name</label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="target_amount" id="editTargetAmount" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target Date</label>
                        <input type="date" class="form-control" name="target_date" id="editTargetDate" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Savings Account</label>
                        <select class="form-select" name="account_id" id="editAccount" required>
                            <option value="">Select Account</option>
                            @foreach($savingsAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" name="color" id="editColor">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Contribution Modal -->
<div class="modal fade" id="contributionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="contributionForm">
                @csrf
                <input type="hidden" name="goal_id" id="contributionGoalId">
                <div class="modal-header">
                    <h5 class="modal-title">Add Contribution to <span id="contributionGoalName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="amount" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">From Account</label>
                        <select class="form-select" name="from_account_id" required>
                            <option value="">Select Account</option>
                            @foreach($allAccounts->where('type', '!=', 'savings') as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->name }} ({{ currency_symbol() }}{{ number_format($account->balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <input type="text" class="form-control" name="description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Contribution</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Savings Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addAccountForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Savings Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Initial Balance</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="balance" step="0.01" min="0" value="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" name="color" value="#10B981">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Add Goal
    $('#addGoalForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("savings.goals.store") }}',
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
    
    // Edit Goal
    $('.edit-goal').on('click', function() {
        const goal = $(this).data('goal');
        $('#editGoalId').val(goal.id);
        $('#editName').val(goal.name);
        $('#editTargetAmount').val(goal.target_amount);
        $('#editTargetDate').val(goal.target_date);
        $('#editAccount').val(goal.account_id);
        $('#editColor').val(goal.color);
    });
    
    $('#editGoalForm').on('submit', function(e) {
        e.preventDefault();
        const goalId = $('#editGoalId').val();
        
        $.ajax({
            url: `/savings/goals/${goalId}`,
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
    
    // Delete Goal
    $('.delete-goal').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this savings goal?')) {
            return;
        }
        
        const goalId = $(this).data('id');
        
        $.ajax({
            url: `/savings/goals/${goalId}`,
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
    
    // Add Contribution
    $('.contribute-btn').on('click', function(e) {
        e.preventDefault();
        const goalId = $(this).data('goal-id');
        const goalName = $(this).data('goal-name');
        
        $('#contributionGoalId').val(goalId);
        $('#contributionGoalName').text(goalName);
        $('#contributionModal').modal('show');
    });
    
    $('#contributionForm').on('submit', function(e) {
        e.preventDefault();
        const goalId = $('#contributionGoalId').val();
        
        $.ajax({
            url: `/savings/goals/${goalId}/contribute`,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.error || xhr.responseJSON?.message || 'An error occurred'));
            }
        });
    });
    
    // Add Savings Account
    $('#addAccountForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("savings.accounts.store") }}',
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
});
</script>
@endsection
