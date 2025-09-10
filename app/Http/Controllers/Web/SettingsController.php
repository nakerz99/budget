<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's categories
        $categories = Category::where('user_id', $user->id)
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');
        
        return view('settings.index', compact('user', 'categories'));
    }
    
    /**
     * Update user profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'full_name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string|max:50',
        ]);
        
        $user->update([
            'full_name' => $request->full_name,
            'currency' => $request->currency,
            'timezone' => $request->timezone,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
        ]);
    }
    
    /**
     * Update user PIN.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePin(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'current_pin' => 'required|string|size:6',
            'new_pin' => 'required|string|size:6|confirmed',
        ]);
        
        // Verify current PIN
        if (!Hash::check($request->current_pin, $user->pin)) {
            return response()->json([
                'error' => 'Current PIN is incorrect',
            ], 400);
        }
        
        $user->update([
            'pin' => Hash::make($request->new_pin),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'PIN updated successfully',
        ]);
    }
    
    /**
     * Store a new category.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:50',
        ]);
        
        $user = Auth::user();
        
        // Check if category already exists
        $exists = Category::where('user_id', $user->id)
            ->where('name', $request->name)
            ->where('type', $request->type)
            ->exists();
            
        if ($exists) {
            return response()->json([
                'error' => 'Category already exists',
            ], 400);
        }
        
        $category = Category::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'type' => $request->type,
            'color' => $request->color,
            'icon' => $request->icon,
            'is_active' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'category' => $category,
        ]);
    }
    
    /**
     * Update a category.
     *
     * @param Request $request
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCategory(Request $request, Category $category)
    {
        // Verify category belongs to user
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);
        
        $category->update([
            'name' => $request->name,
            'color' => $request->color,
            'icon' => $request->icon,
            'is_active' => $request->is_active ?? $category->is_active,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }
    
    /**
     * Delete a category.
     *
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCategory(Category $category)
    {
        // Verify category belongs to user
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Check if category has transactions
        $transactionCount = $category->transactions()->count();
        if ($transactionCount > 0) {
            return response()->json([
                'error' => "Cannot delete category with {$transactionCount} transactions. Please reassign transactions first.",
            ], 400);
        }
        
        // Check if category has budgets
        $budgetCount = $category->budgets()->count();
        if ($budgetCount > 0) {
            return response()->json([
                'error' => "Cannot delete category with {$budgetCount} budget entries. Please delete budgets first.",
            ], 400);
        }
        
        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
    
    /**
     * Export user data.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportData()
    {
        $user = Auth::user();
        
        // Gather all user data
        $data = [
            'user' => $user->only(['username', 'full_name', 'currency', 'timezone', 'created_at']),
            'categories' => $user->categories->toArray(),
            'accounts' => $user->accounts->toArray(),
            'transactions' => $user->transactions()->with(['category', 'account'])->get()->toArray(),
            'budgets' => $user->budgets()->with('category')->get()->toArray(),
            'bills' => $user->bills()->with('category')->get()->toArray(),
            'savings_goals' => $user->savingsGoals()->with('account')->get()->toArray(),
        ];
        
        $filename = 'budget_tracker_export_' . date('Y-m-d') . '.json';
        
        return response()->json($data)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
