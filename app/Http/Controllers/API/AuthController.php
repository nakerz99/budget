<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserApprovalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * User login with username and PIN
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || $user->pin !== $request->pin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Account pending approval'
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token
            ]
        ]);
    }

    /**
     * User registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
            'full_name' => 'required|string|max:255',
            'pin' => 'required|string|size:6',
            'pin_confirmation' => 'required|same:pin',
            'currency' => 'string|size:3',
            'timezone' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'pin' => $request->pin,
            'currency' => $request->currency ?? 'USD',
            'timezone' => $request->timezone ?? 'UTC',
            'is_admin' => false,
            'is_approved' => false,
        ]);

        // Create approval request
        UserApprovalRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Your account is pending admin approval.',
            'data' => [
                'user' => new UserResource($user)
            ]
        ], 201);
    }

    /**
     * User logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get pending approval requests (Admin only)
     */
    public function getPendingApprovals(Request $request)
    {
        if (!$request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $requests = UserApprovalRequest::with('user')
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($requests->pluck('user'))
        ]);
    }

    /**
     * Approve user request (Admin only)
     */
    public function approveUser(Request $request, $userId)
    {
        if (!$request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = User::findOrFail($userId);
        $approvalRequest = $user->approvalRequest;

        if (!$approvalRequest || $approvalRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid approval request'
            ], 400);
        }

        $user->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $approvalRequest->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User approved successfully'
        ]);
    }

    /**
     * Reject user request (Admin only)
     */
    public function rejectUser(Request $request, $userId)
    {
        if (!$request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = User::findOrFail($userId);
        $approvalRequest = $user->approvalRequest;

        if (!$approvalRequest || $approvalRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid approval request'
            ], 400);
        }

        $user->update([
            'rejection_reason' => $request->rejection_reason,
        ]);

        $approvalRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User rejected successfully'
        ]);
    }
}
