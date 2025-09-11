@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">User Management</h1>
                <p class="page-subtitle">Manage all registered users</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-users"></i> All Users
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center">
                                                {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-semibold">{{ $user->full_name }}</div>
                                                <div class="text-sm text-gray-600">{{ $user->username }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->is_admin)
                                            <span class="badge badge-primary">Admin</span>
                                        @elseif($user->is_approved)
                                            <span class="badge badge-success">Approved</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($user->last_login_at)
                                            {{ $user->last_login_at->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            @if(!$user->is_admin && !$user->is_approved)
                                                <form method="POST" action="{{ route('admin.approve', $user) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.reject', $user) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400">No actions</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">No Users Found</h3>
                    <p class="text-gray-500">No users have registered yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
