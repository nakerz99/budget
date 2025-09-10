<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SavingsGoalResource;
use App\Models\SavingsGoal;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SavingsGoalController extends Controller
{
    /**
     * Display a listing of savings goals
     */
    public function index(Request $request)
    {
        $goals = SavingsGoal::where('user_id', $request->user()->id)
            ->with('account')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => SavingsGoalResource::collection($goals)
        ]);
    }

    /**
     * Store a newly created savings goal
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'required|date|after:today',
            'color' => 'string|max:7'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $goal = SavingsGoal::create([
            'user_id' => $request->user()->id,
            'account_id' => $request->account_id,
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'target_date' => $request->target_date,
            'color' => $request->color ?? '#8B5CF6'
        ]);

        return response()->json([
            'success' => true,
            'data' => new SavingsGoalResource($goal->load('account'))
        ], 201);
    }

    /**
     * Display the specified savings goal
     */
    public function show(SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== request()->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new SavingsGoalResource($savingsGoal->load('account'))
        ]);
    }

    /**
     * Update the specified savings goal
     */
    public function update(Request $request, SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'target_amount' => 'numeric|min:0',
            'target_date' => 'date|after:today',
            'color' => 'string|max:7',
            'is_completed' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $savingsGoal->update($request->only(['name', 'target_amount', 'target_date', 'color', 'is_completed']));

        return response()->json([
            'success' => true,
            'data' => new SavingsGoalResource($savingsGoal->load('account'))
        ]);
    }

    /**
     * Remove the specified savings goal
     */
    public function destroy(SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== request()->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $savingsGoal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Savings goal deleted successfully'
        ]);
    }

    /**
     * Add contribution to savings goal
     */
    public function addContribution(Request $request, SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $savingsGoal->increment('current_amount', $request->amount);

        // Check if goal is completed
        if ($savingsGoal->current_amount >= $savingsGoal->target_amount) {
            $savingsGoal->update(['is_completed' => true]);
        }

        return response()->json([
            'success' => true,
            'data' => $savingsGoal->fresh()->load('account')
        ]);
    }
}
