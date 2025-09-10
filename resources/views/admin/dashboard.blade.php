@extends('layouts.app')

@section('title', 'Admin Dashboard - Budget Tracker')

@section('content')
<div>
    <h1 style="font-size: 2.5rem; font-weight: 600; margin-bottom: 2rem;">Admin Dashboard</h1>

    <div class="grid grid-4" style="margin-bottom: 3rem;">
        <div class="card text-center">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #3b82f6;">👥</div>
            <h3 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $stats['total_users'] }}</h3>
            <p style="color: #6b7280;">Total Users</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #f59e0b;">⏳</div>
            <h3 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $stats['pending_approvals'] }}</h3>
            <p style="color: #6b7280;">Pending Approvals</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #059669;">✅</div>
            <h3 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $stats['approved_users'] }}</h3>
            <p style="color: #6b7280;">Approved Users</p>
        </div>

        <div class="card text-center">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #dc2626;">❌</div>
            <h3 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $stats['rejected_users'] }}</h3>
            <p style="color: #6b7280;">Rejected Users</p>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Quick Actions</h2>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <a href="{{ route('admin.pending-approvals') }}" class="btn btn-primary" style="text-align: center;">
                    Review Pending Approvals
                </a>
                <a href="{{ route('admin.users') }}" class="btn btn-secondary" style="text-align: center;">
                    View All Users
                </a>
            </div>
        </div>

        <div class="card">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Recent Activity</h2>
            <p style="color: #6b7280;">No recent activity to display.</p>
        </div>
    </div>
</div>

<style>
.grid-4 {
    grid-template-columns: repeat(4, 1fr);
}

@media (max-width: 768px) {
    .grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .grid-4 {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
