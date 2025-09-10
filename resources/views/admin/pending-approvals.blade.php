@extends('layouts.app')

@section('title', 'Pending Approvals - Admin Dashboard')

@section('content')
<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; font-weight: 600;">Pending Approvals</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            ← Back to Dashboard
        </a>
    </div>

    @if($requests->count() > 0)
        <div class="card">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">User</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Username</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Registered</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 1rem;">
                                    <div>
                                        <div style="font-weight: 600;">{{ $request->user->full_name }}</div>
                                        <div style="color: #6b7280; font-size: 0.875rem;">ID: {{ $request->user->id }}</div>
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="font-family: monospace; background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        {{ $request->user->username }}
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    {{ $request->user->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button 
                                            onclick="approveUser({{ $request->user->id }})" 
                                            class="btn btn-primary" 
                                            style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                        >
                                            Approve
                                        </button>
                                        <button 
                                            onclick="rejectUser({{ $request->user->id }})" 
                                            class="btn btn-secondary" 
                                            style="padding: 0.5rem 1rem; font-size: 0.875rem; background: #dc2626;"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2rem;">
                {{ $requests->links() }}
            </div>
        </div>
    @else
        <div class="card text-center">
            <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">No Pending Approvals</h2>
            <p style="color: #6b7280;">All user registrations have been processed.</p>
        </div>
    @endif
</div>

<!-- Approve User Modal -->
<div id="approveModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
        <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Approve User</h3>
        <form id="approveForm" method="POST">
            @csrf
            <div class="form-group">
                <label for="admin_notes" class="form-label">Admin Notes (Optional)</label>
                <textarea 
                    id="admin_notes" 
                    name="admin_notes" 
                    class="form-input" 
                    rows="3" 
                    placeholder="Add any notes about this approval..."
                ></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeModal('approveModal')" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    Approve User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject User Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
        <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Reject User</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="form-group">
                <label for="rejection_reason" class="form-label">Rejection Reason *</label>
                <textarea 
                    id="rejection_reason" 
                    name="rejection_reason" 
                    class="form-input" 
                    rows="3" 
                    placeholder="Please provide a reason for rejection..."
                    required
                ></textarea>
            </div>
            <div class="form-group">
                <label for="admin_notes" class="form-label">Admin Notes (Optional)</label>
                <textarea 
                    id="admin_notes" 
                    name="admin_notes" 
                    class="form-input" 
                    rows="3" 
                    placeholder="Add any additional notes..."
                ></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeModal('rejectModal')" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-secondary" style="background: #dc2626;">
                    Reject User
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function approveUser(userId) {
        document.getElementById('approveForm').action = `/admin/approve-user/${userId}`;
        document.getElementById('approveModal').style.display = 'block';
    }

    function rejectUser(userId) {
        document.getElementById('rejectForm').action = `/admin/reject-user/${userId}`;
        document.getElementById('rejectModal').style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.id === 'approveModal' || event.target.id === 'rejectModal') {
            event.target.style.display = 'none';
        }
    }
</script>
@endpush
@endsection
