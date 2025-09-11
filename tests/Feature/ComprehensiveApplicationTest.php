<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Bill;
use Illuminate\Support\Facades\Hash;

class ComprehensiveApplicationTest extends TestCase
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
    }

    /** @test */
    public function test_authentication_pages_load_correctly()
    {
        // Test login page
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign In');
        $response->assertSee('Username');
        $response->assertSee('PIN');

        // Test signup page
        $response = $this->get('/signup');
        $response->assertStatus(200);
        $response->assertSee('Join Us Today');
        $response->assertSee('Create Account');
    }

    /** @test */
    public function test_user_can_register_and_login()
    {
        // Test registration
        $response = $this->post('/signup', [
            'username' => 'newuser',
            'full_name' => 'New User',
            'pin' => '123456',
            'pin_confirmation' => '123456'
        ]);
        $response->assertRedirect('/login');

        // Test login
        $response = $this->post('/login', [
            'username' => 'newuser',
            'pin' => '123456'
        ]);
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function test_dashboard_loads_with_correct_data()
    {
        $this->actingAs($this->user);

        // Create some test data
        Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'description' => 'Test Transaction',
            'amount' => -100.00,
            'type' => 'expense',
            'transaction_date' => now()
        ]);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Monthly Spending');
        $response->assertSee('Account Balance');
        $response->assertSee('Monthly Budget');
        $response->assertSee('Savings Goals');
    }

    /** @test */
    public function test_transactions_page_functionality()
    {
        $this->actingAs($this->user);

        // Test transactions index
        $response = $this->get('/transactions');
        $response->assertStatus(200);
        $response->assertSee('Transaction History');
        $response->assertSee('Add Transaction');

        // Test create transaction page
        $response = $this->get('/transactions/create');
        $response->assertStatus(200);
        $response->assertSee('Add New Transaction');

        // Test creating a transaction
        $response = $this->post('/transactions', [
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'description' => 'Test Transaction',
            'amount' => -100.00,
            'type' => 'expense',
            'transaction_date' => now()->format('Y-m-d')
        ]);
        $response->assertRedirect('/transactions');

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => 'Test Transaction',
            'amount' => -100.00
        ]);
    }

    /** @test */
    public function test_budget_page_functionality()
    {
        $this->actingAs($this->user);

        // Test budget index
        $response = $this->get('/budget');
        $response->assertStatus(200);
        $response->assertSee('Budget Overview');
        $response->assertSee('Budget vs Actual');

        // Test create budget page
        $response = $this->get('/budget/create');
        $response->assertStatus(200);
        $response->assertSee('Create New Budget');

        // Test creating a budget
        $response = $this->post('/budget', [
            'category_id' => $this->category->id,
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d')
        ]);
        $response->assertRedirect('/budget');

        // Verify budget was created
        $this->assertDatabaseHas('budgets', [
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 500.00
        ]);
    }

    /** @test */
    public function test_bills_page_functionality()
    {
        $this->actingAs($this->user);

        // Test bills index
        $response = $this->get('/bills');
        $response->assertStatus(200);
        $response->assertSee('Upcoming Bills');
        $response->assertSee('Add Bill');

        // Test create bill page
        $response = $this->get('/bills/create');
        $response->assertStatus(200);
        $response->assertSee('Add New Bill');

        // Test creating a bill
        $response = $this->post('/bills', [
            'name' => 'Test Bill',
            'amount' => 100.00,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'frequency' => 'monthly',
            'is_paid' => false
        ]);
        $response->assertRedirect('/bills');

        // Verify bill was created
        $this->assertDatabaseHas('bills', [
            'user_id' => $this->user->id,
            'name' => 'Test Bill',
            'amount' => 100.00
        ]);
    }

    /** @test */
    public function test_savings_page_functionality()
    {
        $this->actingAs($this->user);

        // Test savings index
        $response = $this->get('/savings');
        $response->assertStatus(200);
        $response->assertSee('Savings Goals');
        $response->assertSee('Add Goal');

        // Test create savings goal page
        $response = $this->get('/savings/create');
        $response->assertStatus(200);
        $response->assertSee('Create New Goal');

        // Test creating a savings goal
        $response = $this->post('/savings', [
            'name' => 'Test Goal',
            'target_amount' => 1000.00,
            'current_amount' => 0.00,
            'target_date' => now()->addMonths(6)->format('Y-m-d'),
            'description' => 'Test savings goal'
        ]);
        $response->assertRedirect('/savings');

        // Verify savings goal was created
        $this->assertDatabaseHas('savings_goals', [
            'user_id' => $this->user->id,
            'name' => 'Test Goal',
            'target_amount' => 1000.00
        ]);
    }

    /** @test */
    public function test_reports_page_functionality()
    {
        $this->actingAs($this->user);

        // Create some test data for reports
        Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'description' => 'Test Income',
            'amount' => 1000.00,
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

        // Test reports index
        $response = $this->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('Financial Reports');
        $response->assertSee('Income vs Expenses');
        $response->assertSee('Category Breakdown');
        $response->assertSee('Monthly Trend');
    }

    /** @test */
    public function test_settings_page_functionality()
    {
        $this->actingAs($this->user);

        // Test settings index
        $response = $this->get('/settings');
        $response->assertStatus(200);
        $response->assertSee('Settings');
        $response->assertSee('Profile');
        $response->assertSee('Security');
        $response->assertSee('Categories');
        $response->assertSee('Application Preferences');
        $response->assertSee('Data Management');
    }

    /** @test */
    public function test_mobile_responsiveness()
    {
        $this->actingAs($this->user);

        // Test all pages with mobile user agent
        $mobileUserAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1';

        $pages = [
            '/dashboard',
            '/transactions',
            '/budget',
            '/bills',
            '/savings',
            '/reports',
            '/settings'
        ];

        foreach ($pages as $page) {
            $response = $this->get($page, ['User-Agent' => $mobileUserAgent]);
            $response->assertStatus(200);
            
            // Check for mobile-friendly elements
            $response->assertSee('bottom-nav'); // Mobile navigation
            $response->assertSee('financial-overview-grid'); // Responsive grid
        }
    }

    /** @test */
    public function test_api_endpoints()
    {
        $this->actingAs($this->user);

        // Test transactions API
        $response = $this->get('/api/v1/transactions');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'description',
                    'amount',
                    'type',
                    'transaction_date'
                ]
            ]
        ]);

        // Test categories API
        $response = $this->get('/api/v1/categories');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'color',
                    'type'
                ]
            ]
        ]);
    }

    /** @test */
    public function test_data_validation()
    {
        $this->actingAs($this->user);

        // Test invalid transaction data
        $response = $this->post('/transactions', [
            'account_id' => 'invalid',
            'category_id' => 'invalid',
            'description' => '',
            'amount' => 'not-a-number',
            'type' => 'invalid',
            'transaction_date' => 'invalid-date'
        ]);
        $response->assertSessionHasErrors();

        // Test invalid budget data
        $response = $this->post('/budget', [
            'category_id' => 'invalid',
            'amount' => 'not-a-number',
            'period' => 'invalid',
            'start_date' => 'invalid-date',
            'end_date' => 'invalid-date'
        ]);
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function test_unauthorized_access()
    {
        // Test accessing protected pages without authentication
        $protectedPages = [
            '/dashboard',
            '/transactions',
            '/budget',
            '/bills',
            '/savings',
            '/reports',
            '/settings'
        ];

        foreach ($protectedPages as $page) {
            $response = $this->get($page);
            $response->assertRedirect('/login');
        }
    }

    /** @test */
    public function test_search_and_filtering()
    {
        $this->actingAs($this->user);

        // Create test transactions
        Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'description' => 'Grocery Shopping',
            'amount' => -50.00,
            'type' => 'expense',
            'transaction_date' => now()
        ]);

        Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'description' => 'Salary',
            'amount' => 2000.00,
            'type' => 'income',
            'transaction_date' => now()
        ]);

        // Test search functionality
        $response = $this->get('/transactions?search=Grocery');
        $response->assertStatus(200);
        $response->assertSee('Grocery Shopping');
        $response->assertDontSee('Salary');

        // Test filtering by type
        $response = $this->get('/transactions?type=income');
        $response->assertStatus(200);
        $response->assertSee('Salary');
        $response->assertDontSee('Grocery Shopping');
    }

    /** @test */
    public function test_pagination()
    {
        $this->actingAs($this->user);

        // Create multiple transactions within current month
        $currentMonth = now()->startOfMonth();
        for ($i = 1; $i <= 25; $i++) {
            Transaction::create([
                'user_id' => $this->user->id,
                'account_id' => $this->account->id,
                'category_id' => $this->category->id,
                'description' => "Transaction {$i}",
                'amount' => -10.00,
                'type' => 'expense',
                'transaction_date' => $currentMonth->copy()->addDays($i % 28) // Spread across month
            ]);
        }

        // Test first page
        $response = $this->get('/transactions');
        $response->assertStatus(200);
        $response->assertSee('Transaction 1');
        $response->assertSee('Transaction 25'); // All 25 should be on first page with 25 per page

        // Test second page (should be empty since we only have 25 transactions)
        $response = $this->get('/transactions?page=2');
        $response->assertStatus(200);
        $response->assertDontSee('Transaction 1'); // First page content shouldn't be on page 2
    }

    /** @test */
    public function test_currency_formatting()
    {
        $this->actingAs($this->user);

        // Test currency symbol function
        $this->assertEquals('₱', currency_symbol());

        // Test number formatting
        $this->assertEquals('1,000.00', number_format(1000, 2));
        $this->assertEquals('1,000', number_format(1000, 0));
    }

    /** @test */
    public function test_date_formatting()
    {
        $this->actingAs($this->user);

        // Test date formatting in transactions
        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'description' => 'Test Transaction',
            'amount' => -100.00,
            'type' => 'expense',
            'transaction_date' => now()
        ]);

        $response = $this->get('/transactions');
        $response->assertStatus(200);
        $response->assertSee(now()->format('M d, Y'));
    }

    /** @test */
    public function test_error_handling()
    {
        $this->actingAs($this->user);

        // Test 405 for non-existent transaction (route exists but method not allowed)
        $response = $this->get('/transactions/999999');
        $response->assertStatus(405);

        // Test 404 for non-existent budget
        $response = $this->get('/budget/999999');
        $response->assertStatus(404);
    }

    /** @test */
    public function test_data_consistency()
    {
        $this->actingAs($this->user);

        // Create a transaction
        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'description' => 'Test Transaction',
            'amount' => -100.00,
            'type' => 'expense',
            'transaction_date' => now()
        ]);

        // Verify account balance is updated
        $this->account->refresh();
        $this->assertEquals(900.00, $this->account->balance);

        // Delete transaction
        $transaction->delete();

        // Verify account balance is restored
        $this->account->refresh();
        $this->assertEquals(1000.00, $this->account->balance);
    }
}
