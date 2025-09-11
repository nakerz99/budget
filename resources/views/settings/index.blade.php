@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div>
    <!-- Header with integrated summary -->
    <div class="mb-4 lg:mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-3 lg:mb-4">
            <!-- Left side: Title -->
            <div class="flex justify-between items-center lg:flex-col lg:items-start lg:gap-2">
                <h1 class="text-xl lg:text-2xl font-bold text-gray-800">Settings</h1>
                <p class="text-sm text-gray-600 hidden lg:block">Manage your account preferences</p>
            </div>
            
            <!-- Right side: Quick Stats -->
            <div class="financial-summary-inline">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-3">
                    <div class="stat-card-inline">
                        <div class="stat-icon-inline">🏷️</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-blue-600">{{ $categories->flatten()->count() }}</div>
                            <div class="stat-label-inline">Categories</div>
                        </div>
                    </div>

                    <div class="stat-card-inline">
                        <div class="stat-icon-inline">💳</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-green-600">{{ $accounts->count() }}</div>
                            <div class="stat-label-inline">Accounts</div>
                        </div>
                    </div>

                    <div class="stat-card-inline">
                        <div class="stat-icon-inline">💰</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-purple-600">{{ currency_symbol() }}{{ number_format($accounts->sum('balance'), 0) }}</div>
                            <div class="stat-label-inline">Total Balance</div>
                        </div>
                    </div>

                    <div class="stat-card-inline">
                        <div class="stat-icon-inline">⚙️</div>
                        <div class="stat-content-inline">
                            <div class="stat-value-inline text-orange-600">6</div>
                            <div class="stat-label-inline">Sections</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Tabs -->
    <div class="lg:hidden mb-4">
        <div class="flex overflow-x-auto space-x-1 bg-gray-100 p-1 rounded-lg">
            <button class="settings-tab-btn active" data-tab="profile">
                <i class="fas fa-user text-xs"></i>
                <span class="text-xs">Profile</span>
            </button>
            <button class="settings-tab-btn" data-tab="security">
                <i class="fas fa-lock text-xs"></i>
                <span class="text-xs">Security</span>
            </button>
            <button class="settings-tab-btn" data-tab="categories">
                <i class="fas fa-tags text-xs"></i>
                <span class="text-xs">Categories</span>
            </button>
            <button class="settings-tab-btn" data-tab="accounts">
                <i class="fas fa-wallet text-xs"></i>
                <span class="text-xs">Accounts</span>
            </button>
            <button class="settings-tab-btn" data-tab="preferences">
                <i class="fas fa-sliders-h text-xs"></i>
                <span class="text-xs">Prefs</span>
            </button>
            <button class="settings-tab-btn" data-tab="data">
                <i class="fas fa-database text-xs"></i>
                <span class="text-xs">Data</span>
            </button>
        </div>
    </div>

    <!-- Desktop Navigation -->
    <div class="hidden lg:block mb-6">
        <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
            <button class="settings-tab-btn active" data-tab="profile">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </button>
            <button class="settings-tab-btn" data-tab="security">
                <i class="fas fa-lock"></i>
                <span>Security</span>
            </button>
            <button class="settings-tab-btn" data-tab="categories">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </button>
            <button class="settings-tab-btn" data-tab="accounts">
                <i class="fas fa-wallet"></i>
                <span>Accounts</span>
            </button>
            <button class="settings-tab-btn" data-tab="preferences">
                <i class="fas fa-sliders-h"></i>
                <span>Preferences</span>
            </button>
            <button class="settings-tab-btn" data-tab="data">
                <i class="fas fa-database"></i>
                <span>Data Management</span>
            </button>
        </div>
    </div>

    <!-- Settings Content -->
    <div>
        <!-- Profile Tab -->
        <div class="settings-content active" id="profile-content">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user"></i> Profile Information
                </div>
                <div class="card-body p-3 lg:p-4">
                    <form id="profileForm" method="POST" action="{{ route('settings.profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="{{ $user->username }}" disabled>
                                <small class="text-gray-500 text-xs">Username cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" value="{{ $user->full_name }}" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Currency</label>
                                <select class="form-select" name="currency">
                                    <option value="PHP" {{ $user->currency == 'PHP' ? 'selected' : '' }}>PHP - Philippine Peso</option>
                                    <option value="USD" {{ $user->currency == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ $user->currency == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ $user->currency == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="JPY" {{ $user->currency == 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                                    <option value="CAD" {{ $user->currency == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                    <option value="AUD" {{ $user->currency == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Timezone</label>
                                <select class="form-select" name="timezone">
                                    <option value="Asia/Manila" {{ $user->timezone == 'Asia/Manila' ? 'selected' : '' }}>Manila (PHT)</option>
                                    <option value="UTC" {{ $user->timezone == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ $user->timezone == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                    <option value="America/Chicago" {{ $user->timezone == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                    <option value="America/Denver" {{ $user->timezone == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                    <option value="America/Los_Angeles" {{ $user->timezone == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    <option value="Europe/London" {{ $user->timezone == 'Europe/London' ? 'selected' : '' }}>London</option>
                                    <option value="Europe/Paris" {{ $user->timezone == 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                    <option value="Asia/Tokyo" {{ $user->timezone == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                                    <option value="Asia/Singapore" {{ $user->timezone == 'Asia/Singapore' ? 'selected' : '' }}>Singapore</option>
                                    <option value="Asia/Hong_Kong" {{ $user->timezone == 'Asia/Hong_Kong' ? 'selected' : '' }}>Hong Kong</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Tab -->
        <div class="settings-content" id="security-content">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lock"></i> Security Settings
                </div>
                <div class="card-body p-3 lg:p-4">
                    <form id="pinForm" method="POST" action="{{ route('settings.pin.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Current PIN</label>
                                <input type="password" class="form-control" name="current_pin" maxlength="6" pattern="\d{6}" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">New PIN</label>
                                <input type="password" class="form-control" name="new_pin" maxlength="6" pattern="\d{6}" required>
                                <small class="text-gray-500 text-xs">Must be exactly 6 digits</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Confirm New PIN</label>
                                <input type="password" class="form-control" name="new_pin_confirmation" maxlength="6" pattern="\d{6}" required>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i>
                                Update PIN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories Tab -->
        <div class="settings-content" id="categories-content">
            <div class="card">
                <div class="card-header">
                    <div class="flex justify-between items-center">
                        <div>
                            <i class="fas fa-tags"></i> Manage Categories
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="showAddCategoryModal()">
                            <i class="fas fa-plus"></i>
                            <span class="hidden sm:inline ml-1">Add Category</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3 lg:p-4">
                    <!-- Expense Categories -->
                    <div class="mb-4">
                        <h6 class="text-sm font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-arrow-down text-red-500 mr-2"></i>
                            Expense Categories
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @forelse($categories->get('expense', []) as $category)
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs" 
                                             style="background: {{ $category->color }}20; color: {{ $category->color }};">
                                            <i class="fas fa-{{ $category->icon }}"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-gray-800 text-sm truncate">{{ $category->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <button onclick="editCategory({{ $category->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <form method="POST" action="{{ route('settings.categories.delete', $category) }}" 
                                              onsubmit="return confirm('Are you sure you want to delete this category?')" 
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
                            @empty
                            <div class="col-span-full text-center py-6">
                                <div class="text-2xl mb-2">🏷️</div>
                                <p class="text-gray-500 text-sm">No expense categories found</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Income Categories -->
                    <div>
                        <h6 class="text-sm font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-arrow-up text-green-500 mr-2"></i>
                            Income Categories
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @forelse($categories->get('income', []) as $category)
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs" 
                                             style="background: {{ $category->color }}20; color: {{ $category->color }};">
                                            <i class="fas fa-{{ $category->icon }}"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-gray-800 text-sm truncate">{{ $category->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <button onclick="editCategory({{ $category->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <form method="POST" action="{{ route('settings.categories.delete', $category) }}" 
                                              onsubmit="return confirm('Are you sure you want to delete this category?')" 
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
                            @empty
                            <div class="col-span-full text-center py-6">
                                <div class="text-2xl mb-2">💰</div>
                                <p class="text-gray-500 text-sm">No income categories found</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Tab -->
        <div class="settings-content" id="accounts-content">
            <div class="card">
                <div class="card-header">
                    <div class="flex justify-between items-center">
                        <div>
                            <i class="fas fa-wallet"></i> Manage Accounts
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="showAddAccountModal()">
                            <i class="fas fa-plus"></i>
                            <span class="hidden sm:inline ml-1">Add Account</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3 lg:p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse($accounts as $account)
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-blue-100 text-blue-600 text-sm">
                                        <i class="fas fa-{{ $account->type === 'checking' ? 'university' : ($account->type === 'savings' ? 'piggy-bank' : 'credit-card') }}"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-gray-800 text-sm truncate">{{ $account->name }}</div>
                                        <div class="text-xs text-gray-500 capitalize">{{ $account->type }}</div>
                                        <div class="text-xs font-semibold text-gray-800">
                                            {{ currency_symbol() }}{{ number_format($account->balance, 0) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button onclick="editAccount({{ $account->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <form method="POST" action="{{ route('accounts.destroy', $account) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete this account?')" 
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
                        @empty
                        <div class="col-span-full text-center py-6">
                            <div class="text-2xl mb-2">💳</div>
                            <p class="text-gray-500 text-sm">No accounts found</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferences Tab -->
        <div class="settings-content" id="preferences-content">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-sliders-h"></i> Application Preferences
                </div>
                <div class="card-body p-3 lg:p-4">
                    <form id="preferencesForm" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Default Transaction Type</label>
                                <select class="form-select" name="default_transaction_type">
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Date Format</label>
                                <select class="form-select" name="date_format">
                                    <option value="Y-m-d">YYYY-MM-DD</option>
                                    <option value="m/d/Y">MM/DD/YYYY</option>
                                    <option value="d/m/Y">DD/MM/YYYY</option>
                                    <option value="M d, Y">Jan 1, 2024</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Number Format</label>
                                <select class="form-select" name="number_format">
                                    <option value="0">No decimals</option>
                                    <option value="1">1 decimal</option>
                                    <option value="2">2 decimals</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Theme</label>
                                <select class="form-select" name="theme">
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Data Management Tab -->
        <div class="settings-content" id="data-content">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-database"></i> Data Management
                </div>
                <div class="card-body p-3 lg:p-4">
                    <div class="space-y-4">
                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <h6 class="text-sm font-semibold text-gray-800 mb-2">Export Data</h6>
                            <p class="text-gray-600 text-xs mb-3">Download your financial data in various formats</p>
                            <div class="flex gap-2">
                                <a href="{{ route('reports.export.csv') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-file-csv"></i>
                                    Export CSV
                                </a>
                                <a href="{{ route('settings.export') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-file-json"></i>
                                    Export JSON
                                </a>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <h6 class="text-sm font-semibold text-gray-800 mb-2">Import Data</h6>
                            <p class="text-gray-600 text-xs mb-3">Import transactions from CSV files</p>
                            <div class="flex gap-2">
                                <input type="file" name="file" accept=".csv" class="form-control text-xs" disabled>
                                <button type="button" class="btn btn-warning btn-sm" onclick="alert('CSV import coming soon!')">
                                    <i class="fas fa-upload"></i>
                                    Import
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-red-50 rounded-lg border border-red-200">
                            <h6 class="text-sm font-semibold text-gray-800 mb-2">Danger Zone</h6>
                            <p class="text-gray-600 text-xs mb-3">Permanently delete all your data</p>
                            <button onclick="confirmDeleteAllData()" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                                Delete All Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Add Category</h3>
                <button onclick="hideAddCategoryModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('settings.categories.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., Groceries" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" class="form-control h-10" value="#3b82f6">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Icon (Optional)</label>
                    <input type="text" name="icon" class="form-control" placeholder="e.g., shopping-cart">
                    <small class="text-gray-500 text-xs">FontAwesome icon name without 'fa-' prefix</small>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideAddCategoryModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Category</h3>
                <button onclick="hideEditCategoryModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" id="editCategoryForm">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" id="editCategoryName" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" id="editCategoryColor" class="form-control h-10">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Icon (Optional)</label>
                    <input type="text" name="icon" id="editCategoryIcon" class="form-control">
                    <small class="text-gray-500 text-xs">FontAwesome icon name without 'fa-' prefix</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="is_active" id="editCategoryActive" value="1" class="mr-2">
                        Active
                    </label>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideEditCategoryModal()" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        Update Category
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
                <h3 class="text-lg font-semibold text-gray-800">Add Account</h3>
                <button onclick="hideAddAccountModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('accounts.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Account Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., BPI Checking" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Account Type</label>
                    <select name="type" class="form-select" required>
                        <option value="checking">Checking Account</option>
                        <option value="savings">Savings Account</option>
                        <option value="credit">Credit Card</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Initial Balance</label>
                    <input type="number" name="balance" class="form-control" placeholder="0.00" step="0.01" value="0" required>
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

<script>
// Settings navigation
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.settings-tab-btn');
    const contentItems = document.querySelectorAll('.settings-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tab buttons and content
            tabButtons.forEach(btn => btn.classList.remove('active'));
            contentItems.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab button
            this.classList.add('active');
            
            // Show corresponding content
            const tabId = this.getAttribute('data-tab');
            const content = document.getElementById(tabId + '-content');
            if (content) {
                content.classList.add('active');
            }
        });
    });
});

// Form submissions
document.getElementById('profileForm').addEventListener('submit', function(e) {
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
            alert('Profile updated successfully!');
        } else {
            alert('Error: ' + (data.message || 'Failed to update profile'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating profile');
    });
});

document.getElementById('pinForm').addEventListener('submit', function(e) {
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
            alert('PIN updated successfully!');
            this.reset();
        } else {
            alert('Error: ' + (data.error || 'Failed to update PIN'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating PIN');
    });
});

document.getElementById('preferencesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Preferences functionality coming soon!');
});

// Modal functions
function showAddCategoryModal() {
    document.getElementById('addCategoryModal').classList.remove('hidden');
}

function hideAddCategoryModal() {
    document.getElementById('addCategoryModal').classList.add('hidden');
}

function showAddAccountModal() {
    document.getElementById('addAccountModal').classList.remove('hidden');
}

function hideAddAccountModal() {
    document.getElementById('addAccountModal').classList.add('hidden');
}

function showEditCategoryModal() {
    document.getElementById('editCategoryModal').classList.remove('hidden');
}

function hideEditCategoryModal() {
    document.getElementById('editCategoryModal').classList.add('hidden');
}

function editCategory(id) {
    // Fetch category data and populate the edit form
    fetch(`/settings/categories/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editCategoryName').value = data.name;
            document.getElementById('editCategoryColor').value = data.color;
            document.getElementById('editCategoryIcon').value = data.icon || '';
            document.getElementById('editCategoryActive').checked = data.is_active;
            document.getElementById('editCategoryForm').action = `/settings/categories/${id}`;
            showEditCategoryModal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading category data');
        });
}

function editAccount(id) {
    alert('Account editing functionality coming soon!');
}

function confirmDeleteAllData() {
    if (confirm('Are you sure you want to delete ALL your data? This action cannot be undone!')) {
        alert('Data deletion functionality coming soon!');
    }
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const modals = ['addCategoryModal', 'editCategoryModal', 'addAccountModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>

<style>
.settings-tab-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    color: #6b7280;
    background: transparent;
    border: none;
    border-radius: 0.5rem;
    transition: all 0.2s;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
}

.settings-tab-btn:hover {
    background-color: #f3f4f6;
    color: #374151;
}

.settings-tab-btn.active {
    background-color: #3b82f6;
    color: white;
}

.settings-tab-btn i {
    width: 1rem;
    text-align: center;
}

.settings-content {
    display: none;
}

.settings-content.active {
    display: block;
}

@media (max-width: 640px) {
    .settings-tab-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .settings-tab-btn span {
        display: none;
    }
    
    .settings-tab-btn i {
        font-size: 0.875rem;
    }
}
</style>
@endsection