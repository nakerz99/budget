<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Get user settings
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'currency' => $user->currency,
                    'timezone' => $user->timezone,
                    'is_admin' => $user->is_admin,
                    'is_approved' => $user->is_approved
                ],
                'categories' => Category::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
            ]
        ]);
    }

    /**
     * Update user settings
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'full_name' => 'string|max:255',
            'currency' => 'string|size:3',
            'timezone' => 'string|max:255',
            'pin' => 'nullable|string|size:6',
            'pin_confirmation' => 'required_with:pin|same:pin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['full_name', 'currency', 'timezone']);
        
        if ($request->has('pin')) {
            $updateData['pin'] = $request->pin;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'currency' => $user->currency,
                    'timezone' => $user->timezone,
                    'is_admin' => $user->is_admin,
                    'is_approved' => $user->is_approved
                ]
            ]
        ]);
    }

    /**
     * Get user categories
     */
    public function categories(Request $request)
    {
        $categories = Category::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Create a new category
     */
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:expense,income',
            'color' => 'string|max:7',
            'icon' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'type' => $request->type,
            'color' => $request->color ?? '#3B82F6',
            'icon' => $request->icon
        ]);

        return response()->json([
            'success' => true,
            'data' => $category
        ], 201);
    }

    /**
     * Update a category
     */
    public function updateCategory(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'type' => 'in:expense,income',
            'color' => 'string|max:7',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->only(['name', 'type', 'color', 'icon', 'is_active']));

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Delete a category
     */
    public function deleteCategory(Category $category)
    {
        if ($category->user_id !== request()->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if category is being used by transactions
        if ($category->transactions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that is being used by transactions'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Get app preferences
     */
    public function preferences(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'currency' => $user->currency,
                'timezone' => $user->timezone,
                'date_format' => 'Y-m-d', // Default format
                'theme' => 'light' // Default theme
            ]
        ]);
    }

    /**
     * Update app preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'currency' => 'string|size:3',
            'timezone' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['currency', 'timezone']));

        return response()->json([
            'success' => true,
            'data' => [
                'currency' => $user->currency,
                'timezone' => $user->timezone
            ]
        ]);
    }
}
