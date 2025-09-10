<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\BudgetController;
use App\Http\Controllers\API\SavingsGoalController;
use App\Http\Controllers\API\BillController;
use App\Http\Controllers\API\AccountController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\SettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        
        // Admin approval routes
        Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
            Route::get('admin/pending-approvals', [AuthController::class, 'pendingApprovals']);
            Route::post('admin/approve-user/{user}', [AuthController::class, 'approveUser']);
            Route::post('admin/reject-user/{user}', [AuthController::class, 'rejectUser']);
        });
    });

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Transactions
        Route::apiResource('transactions', TransactionController::class);
        Route::get('transactions/export/{format}', [TransactionController::class, 'export']);
        
        // Budget
        Route::apiResource('budgets', BudgetController::class);
        Route::get('budget/current', [BudgetController::class, 'current']);
        Route::get('budget/history', [BudgetController::class, 'history']);
        
        // Savings Goals
        Route::apiResource('savings-goals', SavingsGoalController::class);
        Route::post('savings-goals/{savingsGoal}/add-contribution', [SavingsGoalController::class, 'addContribution']);
        
        // Accounts
        Route::apiResource('accounts', AccountController::class);
        
        // Bills
        Route::apiResource('bills', BillController::class);
        Route::post('bills/{bill}/mark-paid', [BillController::class, 'markPaid']);
        Route::get('bills/upcoming', [BillController::class, 'upcoming']);
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('spending', [ReportController::class, 'spending']);
            Route::get('income', [ReportController::class, 'income']);
            Route::get('savings', [ReportController::class, 'savings']);
            Route::get('comprehensive', [ReportController::class, 'comprehensive']);
            Route::get('export/{format}', [ReportController::class, 'export']);
        });
        
        // Categories
        Route::apiResource('categories', CategoryController::class);
        
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);
        
        // Settings
        Route::get('settings', [SettingsController::class, 'index']);
        Route::put('settings', [SettingsController::class, 'update']);
        Route::get('settings/categories', [SettingsController::class, 'categories']);
        Route::post('settings/categories', [SettingsController::class, 'createCategory']);
        Route::put('settings/categories/{category}', [SettingsController::class, 'updateCategory']);
        Route::delete('settings/categories/{category}', [SettingsController::class, 'deleteCategory']);
        Route::get('settings/preferences', [SettingsController::class, 'preferences']);
        Route::put('settings/preferences', [SettingsController::class, 'updatePreferences']);
    });
});
