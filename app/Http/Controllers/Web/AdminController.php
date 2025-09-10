<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserApprovalRequest;
use App\Services\HouseholdDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'pending_approvals' => UserApprovalRequest::where('status', 'pending')->count(),
            'approved_users' => User::where('is_approved', true)->count(),
            'rejected_users' => UserApprovalRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Show all users
     */
    public function users()
    {
        $users = User::with('approvalRequest')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Show pending approvals
     */
    public function pendingApprovals()
    {
        $requests = UserApprovalRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.pending-approvals', compact('requests'));
    }

    /**
     * Approve user
     */
    public function approveUser(Request $request, User $user)
    {
        $approvalRequest = $user->approvalRequest;

        if (!$approvalRequest || $approvalRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Invalid approval request');
        }

        $user->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $approvalRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Create household data for the newly approved user
        $householdService = new HouseholdDataService();
        $householdService->createHouseholdData($user);

        return redirect()->back()
            ->with('success', 'User approved successfully and household data created');
    }

    /**
     * Reject user
     */
    public function rejectUser(Request $request, User $user)
    {
        $approvalRequest = $user->approvalRequest;

        if (!$approvalRequest || $approvalRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Invalid approval request');
        }

        $user->update([
            'rejection_reason' => $request->rejection_reason,
        ]);

        $approvalRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->back()
            ->with('success', 'User rejected successfully');
    }
}
