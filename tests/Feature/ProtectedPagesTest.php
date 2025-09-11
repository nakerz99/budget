<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProtectedPagesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'username' => 'testuser',
            'full_name' => 'Test User',
            'pin' => '123456',
            'is_approved' => true,
            'approved_at' => now()
        ]);
    }

    /**
     * Test dashboard page loads for authenticated user
     */
    public function test_dashboard_page_loads_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
        $response->assertSee('Welcome back, Test User!');
        $response->assertSee('Monthly Spending');
        $response->assertSee('Account Balance');
    }

    /**
     * Test dashboard redirects unauthenticated users to login
     */
    public function test_dashboard_redirects_unauthenticated_users()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    /**
     * Test transactions page loads for authenticated user
     */
    public function test_transactions_page_loads_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/transactions');
        
        $response->assertStatus(200);
        $response->assertViewIs('transactions.index');
        $response->assertSee('Transactions');
        $response->assertSee('Add Transaction');
    }

    /**
     * Test transactions create page loads for authenticated user
     */
    public function test_transactions_create_page_loads_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/transactions/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('transactions.create');
    }

    /**
     * Test budget page loads for authenticated user
     */
    public function test_budget_page_loads_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/budget');
        
        $response->assertStatus(200);
        $response->assertViewIs('budget.index');
        $response->assertSee('Monthly Budget');
        $response->assertSee('Add Budget');
    }

    /**
     * Test bills page loads for authenticated user
     */
    public function test_bills_page_loads_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/bills');
        
        $response->assertStatus(200);
        $response->assertViewIs('bills.index');
        $response->assertSee('Bills & Subscriptions');
        $response->assertSee('Add Bill');
    }

    /**
     * Test savings page loads for authenticated user
     */
    public function test_savings_page_loads_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/savings');
        
        $response->assertStatus(200);
        $response->assertViewIs('savings.index');
        $response->assertSee('Savings Goals');
        $response->assertSee('Add Goal');
    }

    /**
     * Test reports page loads for authenticated user
     */
    public function test_reports_page_loads_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/reports');
        
        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertSee('Financial Reports');
    }

    /**
     * Test settings page loads for authenticated user
     */
    public function test_settings_page_loads_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/settings');
        
        $response->assertStatus(200);
        $response->assertViewIs('settings.index');
        $response->assertSee('Settings');
        $response->assertSee('Profile Information');
    }

    /**
     * Test all protected pages redirect unauthenticated users
     */
    public function test_protected_pages_redirect_unauthenticated_users()
    {
        $protectedRoutes = [
            '/dashboard',
            '/transactions',
            '/transactions/create',
            '/budget',
            '/bills',
            '/savings',
            '/reports',
            '/settings'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Test mobile navigation elements are present
     */
    public function test_mobile_navigation_elements_present()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('bottom-nav', false);
        $response->assertSee('nav-item', false);
        $response->assertSee('fas fa-home', false);
        $response->assertSee('fas fa-exchange-alt', false);
        $response->assertSee('fas fa-chart-pie', false);
        $response->assertSee('fas fa-file-invoice', false);
        $response->assertSee('fas fa-piggy-bank', false);
    }

    /**
     * Test dashboard stats cards are present
     */
    public function test_dashboard_stats_cards_present()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('stats-grid', false);
        $response->assertSee('stat-card', false);
        $response->assertSee('Monthly Spending');
        $response->assertSee('Account Balance');
        $response->assertSee('Monthly Budget');
        $response->assertSee('Savings Goals');
    }

    /**
     * Test dashboard financial overview is present
     */
    public function test_dashboard_financial_overview_present()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Monthly Spending');
        $response->assertSee('Account Balance');
        $response->assertSee('Monthly Budget');
        $response->assertSee('Savings Goals');
    }

    /**
     * Test transactions page has proper filtering elements
     */
    public function test_transactions_page_has_filtering_elements()
    {
        $response = $this->actingAs($this->user)->get('/transactions');
        
        $response->assertStatus(200);
        $response->assertSee('Search');
        $response->assertSee('Type');
        $response->assertSee('Category');
        $response->assertSee('Date Range');
        $response->assertSee('Filter');
        $response->assertSee('Clear');
    }

    /**
     * Test budget page has proper elements
     */
    public function test_budget_page_has_proper_elements()
    {
        $response = $this->actingAs($this->user)->get('/budget');
        
        $response->assertStatus(200);
        $response->assertSee('Monthly Budget');
        $response->assertSee('Select Month');
        $response->assertSee('Add Budget');
        $response->assertSee('Copy Last Month');
    }

    /**
     * Test bills page has proper elements
     */
    public function test_bills_page_has_proper_elements()
    {
        $response = $this->actingAs($this->user)->get('/bills');
        
        $response->assertStatus(200);
        $response->assertSee('Bills & Subscriptions');
        $response->assertSee('Add Bill');
        $response->assertSee('Upcoming Bills');
    }

    /**
     * Test savings page has proper elements
     */
    public function test_savings_page_has_proper_elements()
    {
        $response = $this->actingAs($this->user)->get('/savings');
        
        $response->assertStatus(200);
        $response->assertSee('Savings Goals');
        $response->assertSee('Add Goal');
        $response->assertSee('Active Goals');
    }

    /**
     * Test reports page has proper elements
     */
    public function test_reports_page_has_proper_elements()
    {
        $response = $this->actingAs($this->user)->get('/reports');
        
        $response->assertStatus(200);
        $response->assertSee('Financial Reports');
        $response->assertSee('Income vs Expenses');
        $response->assertSee('Category Breakdown');
    }

    /**
     * Test settings page has proper elements
     */
    public function test_settings_page_has_proper_elements()
    {
        $response = $this->actingAs($this->user)->get('/settings');
        
        $response->assertStatus(200);
        $response->assertSee('Settings');
        $response->assertSee('Profile Information');
        $response->assertSee('Application Preferences');
    }
}
