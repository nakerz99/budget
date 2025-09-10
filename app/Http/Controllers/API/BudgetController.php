<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        $budgets = Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$month])
            ->with('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => BudgetResource::collection($budgets)
        ]);
    }

    /**
     * Store a newly created budget
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'month_year' => 'required|date',
            'rollover' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $budget = Budget::create([
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'month_year' => $request->month_year,
            'rollover' => $request->rollover ?? false
        ]);

        return response()->json([
            'success' => true,
            'data' => new BudgetResource($budget->load('category'))
        ], 201);
    }

    /**
     * Display the specified budget
     */
    public function show(Budget $budget)
    {
        if ($budget->user_id !== request()->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new BudgetResource($budget->load('category'))
        ]);
    }

    /**
     * Update the specified budget
     */
    public function update(Request $request, Budget $budget)
    {
        if ($budget->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'rollover' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $budget->update($request->only(['amount', 'rollover']));

        return response()->json([
            'success' => true,
            'data' => new BudgetResource($budget->load('category'))
        ]);
    }

    /**
     * Remove the specified budget
     */
    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== request()->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $budget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget deleted successfully'
        ]);
    }

    /**
     * Get current month budget
     */
    public function current(Request $request)
    {
        $user = $request->user();
        $currentMonth = Carbon::now()->format('Y-m');
        
        $budgets = Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$currentMonth])
            ->with('category')
            ->get();

        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');

        return response()->json([
            'success' => true,
            'data' => [
                'budgets' => $budgets,
                'total_budget' => $totalBudget,
                'total_spent' => $totalSpent,
                'remaining' => $totalBudget - $totalSpent,
                'percentage_used' => $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0
            ]
        ]);
    }

    /**
     * Get budget history
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $months = $request->get('months', 6);
        
        $budgets = Budget::where('user_id', $user->id)
            ->where('month_year', '>=', Carbon::now()->subMonths($months)->format('Y-m-01'))
            ->with('category')
            ->orderBy('month_year', 'desc')
            ->get()
            ->groupBy(function ($budget) {
                return Carbon::parse($budget->month_year)->format('Y-m');
            });

        return response()->json([
            'success' => true,
            'data' => BudgetResource::collection($budgets)
        ]);
    }
}
