<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * Display the budget page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get the selected month or default to current month
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        
        // Get all expense categories for the user
        $categories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Get budgets for the selected month
        $budgets = Budget::where('user_id', $user->id)
            ->where('month_year', $monthDate)
            ->with('category')
            ->get()
            ->keyBy('category_id');
        
        // Calculate spent amounts for each category
        $spentAmounts = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthDate, $monthDate->copy()->endOfMonth()])
            ->groupBy('category_id')
            ->select('category_id', DB::raw('SUM(ABS(amount)) as total_spent'))
            ->pluck('total_spent', 'category_id');
        
        // Prepare budget data
        $budgetData = [];
        $totalBudget = 0;
        $totalSpent = 0;
        
        foreach ($categories as $category) {
            $budget = $budgets->get($category->id);
            $spent = $spentAmounts->get($category->id, 0);
            $budgetAmount = $budget ? $budget->amount : 0;
            
            $budgetData[] = [
                'category' => $category,
                'budget' => $budget,
                'budget_amount' => $budgetAmount,
                'spent' => $spent,
                'remaining' => $budgetAmount - $spent,
                'percentage' => $budgetAmount > 0 ? round(($spent / $budgetAmount) * 100, 1) : 0,
            ];
            
            $totalBudget += $budgetAmount;
            $totalSpent += $spent;
        }
        
        // Get income for the month
        $monthlyIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthDate, $monthDate->copy()->endOfMonth()])
            ->sum('amount');
        
        // Calculate summary
        $summary = [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'total_remaining' => $totalBudget - $totalSpent,
            'percentage_used' => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0,
            'monthly_income' => $monthlyIncome,
            'net_savings' => $monthlyIncome - $totalSpent,
        ];
        
        // Get available months (months with transactions or budgets)
        $availableMonths = collect();
        
        // Add current month
        $availableMonths->push(Carbon::now()->startOfMonth());
        
        // Get months with budgets
        Budget::where('user_id', $user->id)
            ->select('month_year')
            ->distinct()
            ->get()
            ->each(function ($budget) use ($availableMonths) {
                $availableMonths->push($budget->month_year);
            });
        
        // Get months with transactions
        Transaction::where('user_id', $user->id)
            ->select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month'))
            ->distinct()
            ->get()
            ->each(function ($transaction) use ($availableMonths) {
                $availableMonths->push(Carbon::parse($transaction->month));
            });
        
        $availableMonths = $availableMonths->unique()
            ->sortDesc()
            ->take(12)
            ->map(function ($date) {
                return [
                    'value' => $date->format('Y-m'),
                    'label' => $date->format('F Y'),
                ];
            });
        
        return view('budget.index', compact(
            'budgetData',
            'summary',
            'selectedMonth',
            'monthDate',
            'availableMonths',
            'budgets',
            'categories'
        ));
    }
    
    /**
     * Store or update budgets for a month.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'budgets' => 'required|array',
            'budgets.*.category_id' => 'required|exists:categories,id',
            'budgets.*.amount' => 'required|numeric|min:0',
        ]);
        
        $user = Auth::user();
        $monthDate = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
        
        // Verify all categories belong to user
        $categoryIds = collect($request->budgets)->pluck('category_id');
        $userCategoryIds = Category::where('user_id', $user->id)
            ->whereIn('id', $categoryIds)
            ->pluck('id');
        
        if ($categoryIds->count() !== $userCategoryIds->count()) {
            return response()->json(['error' => 'Invalid category'], 403);
        }
        
        // Update or create budgets
        foreach ($request->budgets as $budgetData) {
            Budget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $budgetData['category_id'],
                    'month_year' => $monthDate,
                ],
                [
                    'amount' => $budgetData['amount'],
                ]
            );
        }
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Budget saved successfully',
            ]);
        }
        
        return redirect()->route('budget.index')
            ->with('success', 'Budget saved successfully');
    }
    
    /**
     * Copy budget from a previous month.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function copyFromMonth(Request $request)
    {
        $request->validate([
            'from_month' => 'required|date_format:Y-m',
            'to_month' => 'required|date_format:Y-m',
        ]);
        
        $user = Auth::user();
        $fromMonth = Carbon::createFromFormat('Y-m', $request->from_month)->startOfMonth();
        $toMonth = Carbon::createFromFormat('Y-m', $request->to_month)->startOfMonth();
        
        // Get budgets from source month
        $sourceBudgets = Budget::where('user_id', $user->id)
            ->where('month_year', $fromMonth)
            ->get();
        
        if ($sourceBudgets->isEmpty()) {
            return response()->json([
                'error' => 'No budget found for the selected month',
            ], 404);
        }
        
        // Copy budgets to target month
        foreach ($sourceBudgets as $sourceBudget) {
            Budget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $sourceBudget->category_id,
                    'month_year' => $toMonth,
                ],
                [
                    'amount' => $sourceBudget->amount,
                    'rollover' => $sourceBudget->rollover,
                ]
            );
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Budget copied successfully',
        ]);
    }
    
    /**
     * Show the form for editing a budget.
     *
     * @param Budget $budget
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Budget $budget)
    {
        $user = Auth::user();
        
        // Verify the budget belongs to the user
        if ($budget->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'id' => $budget->id,
            'category_id' => $budget->category_id,
            'amount' => $budget->amount,
        ]);
    }
    
    /**
     * Update the specified budget.
     *
     * @param Request $request
     * @param Budget $budget
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Budget $budget)
    {
        $user = Auth::user();
        
        // Verify the budget belongs to the user
        if ($budget->user_id !== $user->id) {
            return redirect()->route('budget.index')
                ->with('error', 'Unauthorized access');
        }
        
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
        ]);
        
        // Verify the category belongs to the user
        $category = Category::where('user_id', $user->id)
            ->where('id', $request->category_id)
            ->first();
        
        if (!$category) {
            return redirect()->route('budget.index')
                ->with('error', 'Invalid category');
        }
        
        $budget->update([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
        ]);
        
        return redirect()->route('budget.index')
            ->with('success', 'Budget updated successfully');
    }
    
    /**
     * Remove the specified budget.
     *
     * @param Budget $budget
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Budget $budget)
    {
        $user = Auth::user();
        
        // Verify the budget belongs to the user
        if ($budget->user_id !== $user->id) {
            return redirect()->route('budget.index')
                ->with('error', 'Unauthorized access');
        }
        
        $budget->delete();
        
        return redirect()->route('budget.index')
            ->with('success', 'Budget deleted successfully');
    }
}
