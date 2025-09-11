@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Admin Dashboard</h1>
                <p class="page-subtitle">System overview and management</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.users') }}" class="btn btn-primary">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a href="{{ route('admin.pending-approvals') }}" class="btn btn-warning">
                    <i class="fas fa-clock"></i> Pending Approvals
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-value">{{ $stats['total_users'] }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-value">{{ $stats['pending_approvals'] }}</div>
            <div class="stat-label">Pending Approvals</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value">{{ $stats['approved_users'] }}</div>
            <div class="stat-label">Approved Users</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-value">{{ $stats['rejected_users'] }}</div>
            <div class="stat-label">Rejected Users</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-6">
        <div class="card-header">
            <i class="fas fa-bolt"></i> Quick Actions
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('admin.users') }}" class="btn btn-lg btn-primary btn-full">
                    <i class="fas fa-users"></i>
                    <span>Manage All Users</span>
                </a>
                
                <a href="{{ route('admin.pending-approvals') }}" class="btn btn-lg btn-warning btn-full">
                    <i class="fas fa-clock"></i>
                    <span>Review Pending Approvals</span>
                </a>
                
                <a href="{{ route('admin.dashboard') }}" class="btn btn-lg btn-secondary btn-full">
                    <i class="fas fa-chart-bar"></i>
                    <span>System Reports</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-history"></i> Recent Activity
        </div>
        <div class="card-body">
            <div class="text-center py-8">
                <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Activity Log</h3>
                <p class="text-gray-500">Recent system activity will be displayed here.</p>
            </div>
        </div>
    </div>
</div>
@endsection
