<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
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
     * Test dashboard API endpoint
     */
    public function test_dashboard_api_endpoint()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'monthly_spending',
                'budget_progress',
                'account_balances',
                'savings_progress',
                'recent_transactions',
                'upcoming_bills',
                'spending_by_category'
            ]
        ]);
    }

    /**
     * Test transactions API endpoint
     */
    public function test_transactions_api_endpoint()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'description',
                    'amount',
                    'type',
                    'transaction_date',
                    'category',
                    'account'
                ]
            ]
        ]);
    }

    /**
     * Test budget API endpoint
     */
    public function test_budget_api_endpoint()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/budgets');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'category',
                    'amount',
                    'spent',
                    'remaining'
                ]
            ]
        ]);
    }

    /**
     * Test bills API endpoint
     */
    public function test_bills_api_endpoint()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/bills');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'amount',
                    'due_date',
                    'is_paid',
                    'is_recurring',
                    'category'
                ]
            ]
        ]);
    }

    /**
     * Test savings goals API endpoint
     */
    public function test_savings_goals_api_endpoint()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/savings-goals');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'target_amount',
                    'current_amount',
                    'target_date',
                    'description'
                ]
            ]
        ]);
    }

    /**
     * Test reports API endpoint
     */
    public function test_reports_api_endpoint()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/reports/comprehensive');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'period',
                'financial_summary',
                'spending_analysis',
                'income_analysis',
                'savings_analysis'
            ]
        ]);
    }

    /**
     * Test API endpoints require authentication
     */
    public function test_api_endpoints_require_authentication()
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
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }

    /**
     * Test creating a transaction via API
     */
    public function test_create_transaction_via_api()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $account = Account::factory()->create(['user_id' => $this->user->id]);

        $transactionData = [
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'expense',
            'category_id' => $category->id,
            'account_id' => $account->id,
            'transaction_date' => now()->format('Y-m-d')
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions', $transactionData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'description',
                'amount',
                'type',
                'transaction_date'
            ]
        ]);

        $this->assertDatabaseHas('transactions', [
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'expense'
        ]);
    }

    /**
     * Test creating a budget via API
     */
    public function test_create_budget_via_api()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $budgetData = [
            'category_id' => $category->id,
            'amount' => 500.00,
            'month_year' => now()->format('Y-m-d')
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'category',
                'amount'
            ]
        ]);

        $this->assertDatabaseHas('budgets', [
            'category_id' => $category->id,
            'amount' => 500.00
        ]);
    }

    /**
     * Test creating a bill via API
     */
    public function test_create_bill_via_api()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $billData = [
            'name' => 'Test Bill',
            'amount' => 75.00,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'category_id' => $category->id,
            'is_recurring' => true
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/bills', $billData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'amount',
                'due_date',
                'is_recurring'
            ]
        ]);

        $this->assertDatabaseHas('bills', [
            'name' => 'Test Bill',
            'amount' => 75.00,
            'is_recurring' => true
        ]);
    }

    /**
     * Test creating a savings goal via API
     */
    public function test_create_savings_goal_via_api()
    {
        $account = \App\Models\Account::factory()->create(['user_id' => $this->user->id]);
        
        $goalData = [
            'account_id' => $account->id,
            'name' => 'Test Goal',
            'target_amount' => 1000.00,
            'target_date' => now()->addMonths(6)->format('Y-m-d'),
            'color' => '#3b82f6'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/savings-goals', $goalData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'target_amount',
                'current_amount',
                'target_date'
            ]
        ]);

        $this->assertDatabaseHas('savings_goals', [
            'name' => 'Test Goal',
            'target_amount' => 1000.00,
            'current_amount' => 0.00
        ]);
    }

    /**
     * Test API validation errors
     */
    public function test_api_validation_errors()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'amount',
            'type',
            'category_id',
            'account_id'
        ]);
    }

    /**
     * Test API error handling
     */
    public function test_api_error_handling()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/transactions/999');

        $response->assertStatus(404);
    }
}
