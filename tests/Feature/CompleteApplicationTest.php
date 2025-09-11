<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Bill;
use App\Models\SavingsGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CompleteApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'username' => 'testuser',
            'full_name' => 'Test User',
            'pin' => '123456',
            'is_approved' => true,
            'approved_at' => now()
        ]);
    }

    /**
     * Test complete user journey from signup to dashboard
     */
    public function test_complete_user_journey()
    {
        // 1. User visits landing page
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Take Control of Your Finances');

        // 2. User clicks signup
        $response = $this->get('/signup');
        $response->assertStatus(200);
        $response->assertSee('Sign Up');

        // 3. User signs up
        $userData = [
            'full_name' => 'John Doe',
            'username' => 'johndoe',
            'pin' => '123456',
            'pin_confirmation' => '123456',
            'terms' => '1',
            'privacy' => '1'
        ];

        $response = $this->post('/signup', $userData);
        $response->assertRedirect('/pending-approval');

        // 4. User is redirected to pending approval
        $response = $this->get('/pending-approval');
        $response->assertStatus(200);
        $response->assertSee('Pending Approval');

        // 5. Admin approves user (simulate)
        $user = User::where('username', 'johndoe')->first();
        $user->update(['is_approved' => true, 'approved_at' => now()]);

        // 6. User logs in
        $response = $this->post('/login', [
            'username' => 'johndoe',
            'pin' => '123456'
        ]);
        $response->assertRedirect('/dashboard');

        // 7. User sees dashboard
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Welcome back, John Doe!');
    }

    /**
     * Test complete financial management workflow
     */
    public function test_complete_financial_workflow()
    {
        // Create test data
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Food',
            'color' => '#ff0000'
        ]);

        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Checking Account',
            'balance' => 1000.00
        ]);

        // 1. User creates a transaction
        $transactionData = [
            'description' => 'Grocery Shopping',
            'amount' => 50.00,
            'type' => 'expense',
            'category_id' => $category->id,
            'account_id' => $account->id,
            'transaction_date' => now()->format('Y-m-d')
        ];

        $response = $this->actingAs($this->user)
            ->post('/transactions', $transactionData);

        $response->assertRedirect('/transactions');

        // 2. User views transactions
        $response = $this->actingAs($this->user)->get('/transactions');
        $response->assertStatus(200);
        $response->assertSee('Grocery Shopping');
        $response->assertSee('50.00');

        // 3. User creates a budget
        $budgetData = [
            'month' => now()->format('Y-m'),
            'budgets' => [
                [
                    'category_id' => $category->id,
                    'amount' => 500.00
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post('/budget', $budgetData);

        $response->assertRedirect('/budget');

        // 4. User views budget
        $response = $this->actingAs($this->user)->get('/budget');
        $response->assertStatus(200);
        $response->assertSee('Food');
        $response->assertSee('₱500');

        // 5. User creates a bill
        $billData = [
            'name' => 'Electricity Bill',
            'amount' => 75.00,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'category_id' => $category->id,
            'is_recurring' => true
        ];

        $response = $this->actingAs($this->user)
            ->post('/bills', $billData);

        $response->assertRedirect('/bills');

        // 6. User views bills
        $response = $this->actingAs($this->user)->get('/bills');
        $response->assertStatus(200);
        $response->assertSee('Electricity Bill');
        $response->assertSee('₱75');

        // 7. User creates a savings goal
        $goalData = [
            'name' => 'Emergency Fund',
            'target_amount' => 1000.00,
            'target_date' => now()->addMonths(6)->format('Y-m-d'),
            'account_id' => $account->id,
            'color' => '#8B5CF6'
        ];

        $response = $this->actingAs($this->user)
            ->post('/savings/goals', $goalData);

        $response->assertRedirect('/savings');

        // 8. User views savings goals
        $response = $this->actingAs($this->user)->get('/savings');
        $response->assertStatus(200);
        $response->assertSee('Emergency Fund');
        $response->assertSee('₱1,000');

        // 9. User views reports
        $response = $this->actingAs($this->user)->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('Financial Reports');

        // 10. User views settings
        $response = $this->actingAs($this->user)->get('/settings');
        $response->assertStatus(200);
        $response->assertSee('Settings');
    }

    /**
     * Test mobile responsiveness across all pages
     */
    public function test_mobile_responsiveness_across_all_pages()
    {
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
            $response = $this->actingAs($this->user)->get($page);
            $response->assertStatus(200);
            
            // Check for mobile navigation
            $response->assertSee('bottom-nav', false);
            $response->assertSee('nav-item', false);
            
            // Check for mobile-friendly classes
            $response->assertSee('btn', false);
            $response->assertSee('card', false);
            $response->assertSee('form-control', false);
        }
    }

    /**
     * Test all API endpoints work correctly
     */
    public function test_all_api_endpoints_work()
    {
        $endpoints = [
            '/api/v1/dashboard',
            '/api/v1/transactions',
            '/api/v1/budgets',
            '/api/v1/bills',
            '/api/v1/savings-goals',
            '/api/v1/reports/comprehensive'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson($endpoint);
            
            $response->assertStatus(200);
            $response->assertJsonStructure(['data']);
        }
    }

    /**
     * Test error handling across the application
     */
    public function test_error_handling()
    {
        // Test 404 errors
        $response = $this->actingAs($this->user)->get('/nonexistent-page');
        $response->assertStatus(404);

        // Test validation errors
        $response = $this->actingAs($this->user)
            ->post('/transactions', []);
        $response->assertSessionHasErrors();

        // Test unauthorized access - test without being authenticated
        // Clear any existing authentication
        Auth::logout();
        $this->app['session']->flush();
        
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test data persistence across the application
     */
    public function test_data_persistence()
    {
        // Create test data
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $account = Account::factory()->create(['user_id' => $this->user->id]);

        // Create transaction
        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'expense',
            'category_id' => $category->id,
            'account_id' => $account->id,
            'transaction_date' => now()
        ]);

        // Verify transaction exists
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => 'Test Transaction'
        ]);

        // Create budget
        $budget = Budget::create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'amount' => 500.00,
            'month' => now()->month,
            'year' => now()->year
        ]);

        // Verify budget exists
        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'amount' => 500.00
        ]);

        // Create bill
        $bill = Bill::create([
            'user_id' => $this->user->id,
            'name' => 'Test Bill',
            'amount' => 75.00,
            'due_date' => now()->addDays(7),
            'category_id' => $category->id,
            'is_recurring' => false
        ]);

        // Verify bill exists
        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'name' => 'Test Bill'
        ]);

        // Create savings goal
        $goal = SavingsGoal::create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'name' => 'Test Goal',
            'target_amount' => 1000.00,
            'current_amount' => 0.00,
            'target_date' => now()->addMonths(6),
            'color' => '#8B5CF6'
        ]);

        // Verify savings goal exists
        $this->assertDatabaseHas('savings_goals', [
            'id' => $goal->id,
            'name' => 'Test Goal'
        ]);
    }

    /**
     * Test application performance
     */
    public function test_application_performance()
    {
        $startTime = microtime(true);

        // Test multiple page loads
        $pages = ['/dashboard', '/transactions', '/budget', '/bills', '/savings'];
        
        foreach ($pages as $page) {
            $response = $this->actingAs($this->user)->get($page);
            $response->assertStatus(200);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Assert that all pages load within 2 seconds
        $this->assertLessThan(2.0, $executionTime, 'Application pages should load within 2 seconds');
    }
}
