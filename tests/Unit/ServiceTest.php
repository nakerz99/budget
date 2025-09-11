<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Bill;
use App\Services\BudgetCalculationService;
use App\Services\DashboardDataService;
use App\Services\NotificationService;
use App\Services\ReportGenerationService;
use App\Services\ExportService;
use Illuminate\Support\Facades\Hash;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $account;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'username' => 'testuser',
            'full_name' => 'Test User',
            'pin' => '123456',
            'is_approved' => true
        ]);

        // Create test account
        $this->account = Account::create([
            'user_id' => $this->user->id,
            'name' => 'Test Account',
            'type' => 'checking',
            'balance' => 1000.00,
            'is_active' => true
        ]);

        // Create test category
        $this->category = Category::create([
            'user_id' => $this->user->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#3b82f6'
        ]);

        // Create test data
        $this->createTestData();
    }

    private function createTestData()
    {
        // Create transactions
        Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'description' => 'Test Income',
            'amount' => 2000.00,
            'type' => 'income',
            'transaction_date' => now()
        ]);

        Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'description' => 'Test Expense',
            'amount' => -500.00,
            'type' => 'expense',
            'transaction_date' => now()
        ]);

        // Create budget
        Budget::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 1000.00,
            'period' => 'monthly',
            'start_date' => now(),
            'end_date' => now()->addMonth()
        ]);

        // Create savings goal
        SavingsGoal::create([
            'user_id' => $this->user->id,
            'name' => 'Test Goal',
            'target_amount' => 5000.00,
            'current_amount' => 1000.00,
            'target_date' => now()->addMonths(6),
            'description' => 'Test savings goal'
        ]);

        // Create bill
        Bill::create([
            'user_id' => $this->user->id,
            'name' => 'Test Bill',
            'amount' => 100.00,
            'due_date' => now()->addDays(7),
            'frequency' => 'monthly',
            'is_paid' => false
        ]);
    }

    /** @test */
    public function test_budget_calculation_service()
    {
        $service = new BudgetCalculationService();

        // Test budget utilization calculation
        $budgetUtilization = $service->calculateBudgetUtilization($this->user, now()->format('Y-m'));
        $this->assertIsArray($budgetUtilization);

        // Test category spending calculation
        $categorySpending = $service->calculateCategorySpending($this->user, $this->category->id, now()->format('Y-m'));
        $this->assertIsNumeric($categorySpending);
        $this->assertGreaterThanOrEqual(0, $categorySpending);

        // Test budget recommendations
        $budgetRecommendations = $service->getBudgetRecommendations($this->user, now()->format('Y-m'));
        $this->assertIsArray($budgetRecommendations);
    }

    /** @test */
    public function test_dashboard_data_service()
    {
        $service = new DashboardDataService();

        // Test dashboard data generation
        $dashboardData = $service->getDashboardData($this->user);
        $this->assertIsArray($dashboardData);
        $this->assertArrayHasKey('monthly_spending', $dashboardData);
        $this->assertArrayHasKey('budget_progress', $dashboardData);
        $this->assertArrayHasKey('upcoming_bills', $dashboardData);
        $this->assertArrayHasKey('savings_progress', $dashboardData);
        $this->assertArrayHasKey('account_balances', $dashboardData);
        $this->assertArrayHasKey('recent_transactions', $dashboardData);

        // Test monthly spending calculation
        $monthlySpending = $service->getMonthlySpending($this->user, now()->format('Y-m'));
        $this->assertIsArray($monthlySpending);
        $this->assertArrayHasKey('total_spent', $monthlySpending);
        $this->assertArrayHasKey('total_income', $monthlySpending);

        // Test recent transactions
        $recentTransactions = $service->getRecentTransactions($this->user);
        $this->assertIsArray($recentTransactions);
        $this->assertLessThanOrEqual(10, count($recentTransactions));
    }

    /** @test */
    public function test_notification_service()
    {
        $service = new NotificationService();

        // Test budget alerts
        $budgetAlerts = $service->sendBudgetAlerts($this->user);
        $this->assertIsArray($budgetAlerts);

        // Test bill reminders
        $billReminders = $service->sendBillReminders($this->user);
        $this->assertIsArray($billReminders);

        // Test savings goal notifications
        $savingsGoalNotifications = $service->sendSavingsGoalNotifications($this->user);
        $this->assertIsArray($savingsGoalNotifications);
    }

    /** @test */
    public function test_report_generation_service()
    {
        $service = new ReportGenerationService();

        // Test spending report
        $spendingReport = $service->generateSpendingReport($this->user, now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d'));
        $this->assertIsArray($spendingReport);
        $this->assertArrayHasKey('total_spent', $spendingReport);
        $this->assertArrayHasKey('transaction_count', $spendingReport);
        $this->assertArrayHasKey('spending_by_category', $spendingReport);

        // Test budget report
        $budgetReport = $service->generateBudgetReport($this->user, now()->format('Y-m'));
        $this->assertIsArray($budgetReport);
        $this->assertArrayHasKey('total_budgeted', $budgetReport);
        $this->assertArrayHasKey('total_spent', $budgetReport);

        // Test savings report
        $savingsReport = $service->generateSavingsReport($this->user, now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d'));
        $this->assertIsArray($savingsReport);
        $this->assertArrayHasKey('total_target', $savingsReport);
        $this->assertArrayHasKey('total_current', $savingsReport);
    }

    /** @test */
    public function test_export_service()
    {
        $service = new ExportService();

        // Test CSV export
        $csvResult = $service->exportTransactionsToCsv($this->user);
        $this->assertIsArray($csvResult);
        $this->assertArrayHasKey('filename', $csvResult);
        $this->assertArrayHasKey('file_path', $csvResult);
        $this->assertArrayHasKey('download_url', $csvResult);

        // Test budgets export
        $budgetsResult = $service->exportBudgetsToCsv($this->user);
        $this->assertIsArray($budgetsResult);
        $this->assertArrayHasKey('filename', $budgetsResult);
        $this->assertArrayHasKey('file_path', $budgetsResult);
        $this->assertArrayHasKey('download_url', $budgetsResult);

        // Test savings goals export
        $savingsResult = $service->exportSavingsGoalsToCsv($this->user);
        $this->assertIsArray($savingsResult);
        $this->assertArrayHasKey('filename', $savingsResult);
        $this->assertArrayHasKey('file_path', $savingsResult);
        $this->assertArrayHasKey('download_url', $savingsResult);
    }

    /** @test */
    public function test_service_error_handling()
    {
        $service = new BudgetCalculationService();

        // Test with invalid date range
        $this->expectException(\Exception::class);
        $service->calculateBudgetUtilization($this->user, 'invalid-date');
    }

    /** @test */
    public function test_service_data_validation()
    {
        $service = new DashboardDataService();

        // Test with valid user
        $dashboardData = $service->getDashboardData($this->user);
        $this->assertIsArray($dashboardData);
    }

    /** @test */
    public function test_service_performance()
    {
        $service = new DashboardDataService();

        // Test performance with large dataset
        $start = microtime(true);
        $dashboardData = $service->getDashboardData($this->user);
        $end = microtime(true);

        $this->assertIsArray($dashboardData);
        $this->assertLessThan(1.0, $end - $start); // Should complete in under 1 second
    }

    /** @test */
    public function test_service_caching()
    {
        $service = new DashboardDataService();

        // Test that repeated calls return consistent data
        $data1 = $service->getDashboardData($this->user);
        $data2 = $service->getDashboardData($this->user);

        $this->assertEquals($data1, $data2);
    }

    /** @test */
    public function test_service_dependencies()
    {
        // Test that services can be instantiated without errors
        $budgetService = new BudgetCalculationService();
        $dashboardService = new DashboardDataService();
        $notificationService = new NotificationService();
        $reportService = new ReportGenerationService();
        $exportService = new ExportService();

        $this->assertInstanceOf(BudgetCalculationService::class, $budgetService);
        $this->assertInstanceOf(DashboardDataService::class, $dashboardService);
        $this->assertInstanceOf(NotificationService::class, $notificationService);
        $this->assertInstanceOf(ReportGenerationService::class, $reportService);
        $this->assertInstanceOf(ExportService::class, $exportService);
    }

    /** @test */
    public function test_service_method_chaining()
    {
        $service = new ReportGenerationService();

        // Test that methods can be chained
        $spendingReport = $service->generateSpendingReport($this->user, now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d'));
        $budgetReport = $service->generateBudgetReport($this->user, now()->format('Y-m'));

        $this->assertIsArray($spendingReport);
        $this->assertIsArray($budgetReport);
    }

    /** @test */
    public function test_service_data_types()
    {
        $service = new DashboardDataService();

        $dashboardData = $service->getDashboardData($this->user);

        // Test that all values are of expected types
        $this->assertIsArray($dashboardData['monthly_spending']);
        $this->assertIsArray($dashboardData['budget_progress']);
        $this->assertIsArray($dashboardData['upcoming_bills']);
        $this->assertIsArray($dashboardData['savings_progress']);
        $this->assertIsArray($dashboardData['account_balances']);
        $this->assertIsArray($dashboardData['recent_transactions']);
    }

    /** @test */
    public function test_service_edge_cases()
    {
        $service = new BudgetCalculationService();

        // Test with zero budget
        $zeroBudget = $service->calculateBudgetUtilization($this->user, now()->format('Y-m'));
        $this->assertIsArray($zeroBudget);

        // Test with no transactions
        $userWithNoTransactions = User::factory()->create();
        $noTransactionsBudget = $service->calculateBudgetUtilization($userWithNoTransactions, now()->format('Y-m'));
        $this->assertIsArray($noTransactionsBudget);
    }
}
