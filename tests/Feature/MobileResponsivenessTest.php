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

class MobileResponsivenessTest extends TestCase
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
            'account_id' => $this->account->id,
            'name' => 'Test Goal',
            'target_amount' => 5000.00,
            'current_amount' => 1000.00,
            'target_date' => now()->addMonths(6)
        ]);

        // Create bill
        Bill::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'name' => 'Test Bill',
            'amount' => 100.00,
            'due_date' => now()->addDays(7),
            'frequency' => 'monthly',
            'is_paid' => false
        ]);
    }

    /** @test */
    public function test_dashboard_mobile_responsiveness()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Check for mobile-friendly grid layouts
        $response->assertSee('financial-overview-grid');
        $response->assertSee('stats-grid-2col');
        
        // Check for responsive classes
        $response->assertSee('lg:hidden'); // Mobile-specific elements
        $response->assertSee('hidden lg:block'); // Desktop-specific elements
        
        // Check for mobile-friendly card layouts
        $response->assertSee('card');
        $response->assertSee('stat-card');
    }

    /** @test */
    public function test_transactions_mobile_responsiveness()
    {
        $this->actingAs($this->user);

        $response = $this->get('/transactions');
        $response->assertStatus(200);

        // Check for mobile-friendly transaction list
        $response->assertSee('transaction-item');
        $response->assertSee('lg:hidden'); // Mobile card view
        $response->assertSee('hidden lg:block'); // Desktop table view
        
        // Check for responsive filter buttons
        $response->assertSee('flex flex-col sm:flex-row');
        
        // Check for mobile-friendly action buttons
        $response->assertSee('sm:hidden ml-1'); // Mobile text labels
    }

    /** @test */
    public function test_budget_mobile_responsiveness()
    {
        $this->actingAs($this->user);

        $response = $this->get('/budget');
        $response->assertStatus(200);

        // Check for mobile-friendly budget comparison
        $response->assertSee('budget-comparison-grid');
        $response->assertSee('budget-comparison-card');
        
        // Check for responsive grid layouts
        $response->assertSee('grid grid-cols-1 md:grid-cols-2');
        
        // Check for mobile-friendly cards
        $response->assertSee('card');
    }

    /** @test */
    public function test_bills_mobile_responsiveness()
    {
        $this->actingAs($this->user);

        $response = $this->get('/bills');
        $response->assertStatus(200);

        // Check for mobile-friendly bill items
        $response->assertSee('bill-item');
        $response->assertSee('flex flex-col sm:flex-row');
        
        // Check for responsive layouts
        $response->assertSee('grid grid-cols-1 md:grid-cols-2');
        
        // Check for mobile-friendly buttons
        $response->assertSee('btn btn-sm');
    }

    /** @test */
    public function test_savings_mobile_responsiveness()
    {
        $this->actingAs($this->user);

        $response = $this->get('/savings');
        $response->assertStatus(200);

        // Check for mobile-friendly savings goals
        $response->assertSee('savings-goal-item');
        $response->assertSee('flex flex-col sm:flex-row');
        
        // Check for responsive grid layouts
        $response->assertSee('grid grid-cols-1 md:grid-cols-2');
        
        // Check for mobile-friendly progress bars
        $response->assertSee('progress-bar');
    }

    /** @test */
    public function test_reports_mobile_responsiveness()
    {
        $this->actingAs($this->user);

        $response = $this->get('/reports');
        $response->assertStatus(200);

        // Check for mobile-friendly charts
        $response->assertSee('chart-container');
        $response->assertSee('h-64'); // Chart height
        
        // Check for responsive grid layouts
        $response->assertSee('grid grid-cols-1 lg:grid-cols-2');
        
        // Check for mobile-friendly cards
        $response->assertSee('card');
    }

    /** @test */
    public function test_settings_mobile_responsiveness()
    {
        $this->actingAs($this->user);

        $response = $this->get('/settings');
        $response->assertStatus(200);

        // Check for mobile-friendly navigation
        $response->assertSee('flex lg:flex-col');
        $response->assertSee('lg:col-span-1'); // Navigation column
        $response->assertSee('lg:col-span-3'); // Content column
        
        // Check for responsive forms
        $response->assertSee('grid md:grid-cols-2');
        
        // Check for mobile-friendly tabs
        $response->assertSee('tab-content');
        $response->assertSee('tab-pane');
    }

    /** @test */
    public function test_mobile_navigation()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Check for mobile navigation elements
        $response->assertSee('bottom-nav');
        $response->assertSee('nav-item');
        
        // Check for mobile-friendly icons
        $response->assertSee('fas fa-home');
        $response->assertSee('fas fa-list');
        $response->assertSee('fas fa-chart-pie');
        $response->assertSee('fas fa-cog');
    }

    /** @test */
    public function test_mobile_forms()
    {
        $this->actingAs($this->user);

        // Test transaction form
        $response = $this->get('/transactions/create');
        $response->assertStatus(200);
        $response->assertSee('form-group');
        $response->assertSee('form-control');
        
        // Test budget form
        $response = $this->get('/budget/create');
        $response->assertStatus(200);
        $response->assertSee('form-group');
        $response->assertSee('form-control');
        
        // Test bill form
        $response = $this->get('/bills/create');
        $response->assertStatus(200);
        $response->assertSee('form-group');
        $response->assertSee('form-control');
        
        // Test savings form
        $response = $this->get('/savings/create');
        $response->assertStatus(200);
        $response->assertSee('form-group');
        $response->assertSee('form-control');
    }

    /** @test */
    public function test_mobile_buttons_and_actions()
    {
        $this->actingAs($this->user);

        $response = $this->get('/transactions');
        $response->assertStatus(200);

        // Check for mobile-friendly button layouts
        $response->assertSee('btn-group');
        $response->assertSee('btn btn-sm');
        
        // Check for responsive button text
        $response->assertSee('sm:hidden ml-1'); // Mobile text labels
        
        // Check for mobile-friendly action buttons
        $response->assertSee('fas fa-edit');
        $response->assertSee('fas fa-trash');
    }

    /** @test */
    public function test_mobile_tables_and_lists()
    {
        $this->actingAs($this->user);

        $response = $this->get('/transactions');
        $response->assertStatus(200);

        // Check for mobile-friendly table alternatives
        $response->assertSee('hidden lg:block'); // Desktop table
        $response->assertSee('lg:hidden'); // Mobile cards
        
        // Check for responsive list items
        $response->assertSee('transaction-item');
        $response->assertSee('flex items-start gap-4');
    }

    /** @test */
    public function test_mobile_charts_and_visualizations()
    {
        $this->actingAs($this->user);

        $response = $this->get('/reports');
        $response->assertStatus(200);

        // Check for mobile-friendly chart containers
        $response->assertSee('chart-container');
        $response->assertSee('h-64'); // Fixed height for mobile
        
        // Check for responsive chart layouts
        $response->assertSee('grid grid-cols-1 lg:grid-cols-2');
        
        // Check for mobile-friendly chart controls
        $response->assertSee('chart-controls');
    }

    /** @test */
    public function test_mobile_error_handling()
    {
        $this->actingAs($this->user);

        // Test 404 page
        $response = $this->get('/nonexistent-page');
        $response->assertStatus(404);
        $response->assertSee('Not Found');
        
        // Test validation errors
        $response = $this->post('/transactions', []);
        $response->assertSessionHasErrors();
        
        // Check for mobile-friendly error messages
        $response->assertSee('alert');
        $response->assertSee('error');
    }

    /** @test */
    public function test_mobile_performance()
    {
        $this->actingAs($this->user);

        // Test page load times
        $start = microtime(true);
        $response = $this->get('/dashboard');
        $end = microtime(true);
        
        $response->assertStatus(200);
        $this->assertLessThan(2.0, $end - $start); // Should load in under 2 seconds
        
        // Test with mobile user agent
        $mobileUserAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1';
        
        $start = microtime(true);
        $response = $this->get('/dashboard', ['User-Agent' => $mobileUserAgent]);
        $end = microtime(true);
        
        $response->assertStatus(200);
        $this->assertLessThan(2.0, $end - $start); // Should load in under 2 seconds on mobile
    }

    /** @test */
    public function test_mobile_accessibility()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Check for accessibility attributes
        $response->assertSee('aria-label');
        $response->assertSee('role=');
        
        // Check for proper heading structure
        $response->assertSee('<h1');
        $response->assertSee('<h2');
        
        // Check for proper form labels
        $response->assertSee('<label');
        
        // Check for proper button types
        $response->assertSee('type="submit"');
        $response->assertSee('type="button"');
    }

    /** @test */
    public function test_mobile_touch_interactions()
    {
        $this->actingAs($this->user);

        $response = $this->get('/transactions');
        $response->assertStatus(200);

        // Check for touch-friendly button sizes
        $response->assertSee('btn btn-lg'); // Large buttons for touch
        $response->assertSee('btn btn-sm'); // Small buttons where appropriate
        
        // Check for touch-friendly spacing
        $response->assertSee('gap-2');
        $response->assertSee('gap-4');
        
        // Check for touch-friendly form elements
        $response->assertSee('form-control');
        $response->assertSee('form-select');
    }
}