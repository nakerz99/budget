<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TransactionController;
use App\Http\Controllers\Web\BudgetController;
use App\Http\Controllers\Web\BillController;
use App\Http\Controllers\Web\SavingsController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public Routes
Route::get('/', [AuthController::class, 'landing'])->name('landing');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup'])->name('signup.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// User Status Pages
Route::get('/pending-approval', [AuthController::class, 'pendingApproval'])->name('pending-approval');
Route::get('/account-rejected', [AuthController::class, 'accountRejected'])->name('account-rejected');
Route::get('/account-approved', [AuthController::class, 'accountApproved'])->name('account-approved');

// Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::post('/transactions/bulk-delete', [TransactionController::class, 'bulkDelete'])->name('transactions.bulkDelete');
    
    // Budget
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
    Route::post('/budget/copy', [BudgetController::class, 'copyFromMonth'])->name('budget.copy');
    
    // Bills
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
    Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');
    Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
    Route::post('/bills/{bill}/mark-paid', [BillController::class, 'markPaid'])->name('bills.markPaid');
    Route::post('/bills/{bill}/mark-unpaid', [BillController::class, 'markUnpaid'])->name('bills.markUnpaid');
    
    // Savings
    Route::get('/savings', [SavingsController::class, 'index'])->name('savings.index');
    Route::post('/savings/goals', [SavingsController::class, 'storeGoal'])->name('savings.goals.store');
    Route::put('/savings/goals/{savingsGoal}', [SavingsController::class, 'updateGoal'])->name('savings.goals.update');
    Route::delete('/savings/goals/{savingsGoal}', [SavingsController::class, 'destroyGoal'])->name('savings.goals.destroy');
    Route::post('/savings/goals/{savingsGoal}/contribute', [SavingsController::class, 'addContribution'])->name('savings.goals.contribute');
    Route::post('/savings/accounts', [SavingsController::class, 'storeAccount'])->name('savings.accounts.store');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/settings/pin', [SettingsController::class, 'updatePin'])->name('settings.pin.update');
    Route::post('/settings/categories', [SettingsController::class, 'storeCategory'])->name('settings.categories.store');
    Route::put('/settings/categories/{category}', [SettingsController::class, 'updateCategory'])->name('settings.categories.update');
    Route::delete('/settings/categories/{category}', [SettingsController::class, 'deleteCategory'])->name('settings.categories.delete');
    Route::get('/settings/export', [SettingsController::class, 'exportData'])->name('settings.export');
    
    // Admin Routes
    Route::middleware(['is_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/pending-approvals', [AdminController::class, 'pendingApprovals'])->name('pending-approvals');
        Route::post('/approve-user/{user}', [AdminController::class, 'approveUser'])->name('approve-user');
        Route::post('/reject-user/{user}', [AdminController::class, 'rejectUser'])->name('reject-user');
    });
});
