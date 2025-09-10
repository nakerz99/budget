@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Settings</h1>
        </div>
    </div>

    <div class="row">
        <!-- Settings Navigation -->
        <div class="col-md-3">
            <div class="list-group mb-4">
                <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-lock"></i> Security
                </a>
                <a href="#categories" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="#preferences" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-cog"></i> Preferences
                </a>
                <a href="#data" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-database"></i> Data Management
                </a>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form id="profileForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="{{ $user->username }}" disabled>
                                    <small class="text-muted">Username cannot be changed</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" value="{{ $user->full_name }}" required>
                                </div>
                                
                                <div class="mb-3">
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
                                
                                <div class="mb-3">
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
                                
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-pane fade" id="security">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Security Settings</h5>
                        </div>
                        <div class="card-body">
                            <h6>Change PIN</h6>
                            <form id="pinForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Current PIN</label>
                                    <input type="password" class="form-control" name="current_pin" maxlength="6" pattern="\d{6}" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">New PIN</label>
                                    <input type="password" class="form-control" name="new_pin" maxlength="6" pattern="\d{6}" required>
                                    <small class="text-muted">Must be exactly 6 digits</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirm New PIN</label>
                                    <input type="password" class="form-control" name="new_pin_confirmation" maxlength="6" pattern="\d{6}" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update PIN</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Categories Tab -->
                <div class="tab-pane fade" id="categories">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Manage Categories</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-plus"></i> Add Category
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Expense Categories -->
                            <h6 class="mb-3">Expense Categories</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Color</th>
                                            <th>Icon</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($categories->get('expense', []) as $category)
                                        <tr>
                                            <td>{{ $category->name }}</td>
                                            <td>
                                                <span class="badge" style="background-color: {{ $category->color }};">
                                                    {{ $category->color }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($category->icon)
                                                    <i class="fas fa-{{ $category->icon }}"></i>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($category->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary edit-category" 
                                                        data-category='@json($category)'
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editCategoryModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-category" 
                                                        data-id="{{ $category->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No expense categories</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Income Categories -->
                            <h6 class="mb-3">Income Categories</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Color</th>
                                            <th>Icon</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($categories->get('income', []) as $category)
                                        <tr>
                                            <td>{{ $category->name }}</td>
                                            <td>
                                                <span class="badge" style="background-color: {{ $category->color }};">
                                                    {{ $category->color }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($category->icon)
                                                    <i class="fas fa-{{ $category->icon }}"></i>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($category->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary edit-category" 
                                                        data-category='@json($category)'
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editCategoryModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-category" 
                                                        data-id="{{ $category->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No income categories</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preferences Tab -->
                <div class="tab-pane fade" id="preferences">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">App Preferences</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">App preferences coming soon...</p>
                            <ul>
                                <li>Date format selection</li>
                                <li>Number format preferences</li>
                                <li>Email notification settings</li>
                                <li>Dashboard widget preferences</li>
                                <li>Theme selection (light/dark)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Data Management Tab -->
                <div class="tab-pane fade" id="data">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Data Management</h5>
                        </div>
                        <div class="card-body">
                            <h6>Export Data</h6>
                            <p>Download all your data in JSON format for backup or migration purposes.</p>
                            <a href="{{ route('settings.export') }}" class="btn btn-success mb-4">
                                <i class="fas fa-download"></i> Export All Data
                            </a>
                            
                            <hr>
                            
                            <h6 class="text-danger">Danger Zone</h6>
                            <p>Once you delete your account, there is no going back. Please be certain.</p>
                            <button class="btn btn-danger" disabled>
                                <i class="fas fa-trash"></i> Delete Account (Coming Soon)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addCategoryForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type" required>
                            <option value="">Select Type</option>
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" name="color" value="#3B82F6" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Icon (Optional)</label>
                        <select class="form-select" name="icon">
                            <option value="">No Icon</option>
                            <option value="shopping-cart">🛒 Shopping Cart</option>
                            <option value="home">🏠 Home</option>
                            <option value="car">🚗 Car</option>
                            <option value="utensils">🍴 Food</option>
                            <option value="film">🎬 Entertainment</option>
                            <option value="heartbeat">❤️ Health</option>
                            <option value="graduation-cap">🎓 Education</option>
                            <option value="plane">✈️ Travel</option>
                            <option value="gift">🎁 Gifts</option>
                            <option value="dollar-sign">💵 Money</option>
                            <option value="briefcase">💼 Work</option>
                            <option value="chart-line">📈 Investment</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="category_id" id="editCategoryId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" id="editCategoryName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" class="form-control" id="editCategoryType" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" name="color" id="editCategoryColor" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Icon (Optional)</label>
                        <select class="form-select" name="icon" id="editCategoryIcon">
                            <option value="">No Icon</option>
                            <option value="shopping-cart">🛒 Shopping Cart</option>
                            <option value="home">🏠 Home</option>
                            <option value="car">🚗 Car</option>
                            <option value="utensils">🍴 Food</option>
                            <option value="film">🎬 Entertainment</option>
                            <option value="heartbeat">❤️ Health</option>
                            <option value="graduation-cap">🎓 Education</option>
                            <option value="plane">✈️ Travel</option>
                            <option value="gift">🎁 Gifts</option>
                            <option value="dollar-sign">💵 Money</option>
                            <option value="briefcase">💼 Work</option>
                            <option value="chart-line">📈 Investment</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editCategoryActive">
                            <label class="form-check-label" for="editCategoryActive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Profile Form
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("settings.profile.update") }}',
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
    
    // PIN Form
    $('#pinForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("settings.pin.update") }}',
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#pinForm')[0].reset();
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to update PIN'));
            }
        });
    });
    
    // Add Category
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("settings.categories.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to create category'));
            }
        });
    });
    
    // Edit Category
    $('.edit-category').on('click', function() {
        const category = $(this).data('category');
        $('#editCategoryId').val(category.id);
        $('#editCategoryName').val(category.name);
        $('#editCategoryType').val(category.type);
        $('#editCategoryColor').val(category.color);
        $('#editCategoryIcon').val(category.icon || '');
        $('#editCategoryActive').prop('checked', category.is_active);
    });
    
    $('#editCategoryForm').on('submit', function(e) {
        e.preventDefault();
        const categoryId = $('#editCategoryId').val();
        
        $.ajax({
            url: `/settings/categories/${categoryId}`,
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
    
    // Delete Category
    $('.delete-category').on('click', function() {
        if (!confirm('Are you sure you want to delete this category?')) {
            return;
        }
        
        const categoryId = $(this).data('id');
        
        $.ajax({
            url: `/settings/categories/${categoryId}`,
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
                alert('Error: ' + (xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to delete category'));
            }
        });
    });
});
</script>
@endsection
