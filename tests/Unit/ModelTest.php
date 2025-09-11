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
use Illuminate\Support\Facades\Hash;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_user_model()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'full_name' => 'Test User',
            'pin' => '123456',
            'is_approved' => true
        ]);

        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('Test User', $user->full_name);
        $this->assertTrue($user->is_approved);
        $this->assertEquals('123456', $user->pin);
    }

    /** @test */
    public function test_account_model()
    {
        $user = User::factory()->create();
        
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Account',
            'type' => 'checking',
            'balance' => 1000.00,
            'is_active' => true
        ]);

        $this->assertEquals($user->id, $account->user_id);
        $this->assertEquals('Test Account', $account->name);
        $this->assertEquals('checking', $account->type);
        $this->assertEquals(1000.00, $account->balance);
        $this->assertTrue($account->is_active);
    }

    /** @test */
    public function test_category_model()
    {
        $user = User::factory()->create();
        
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#3b82f6'
        ]);

        $this->assertEquals($user->id, $category->user_id);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('expense', $category->type);
        $this->assertEquals('#3b82f6', $category->color);
    }

    /** @test */
    public function test_transaction_model()
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Account',
            'type' => 'checking',
            'balance' => 1000.00,
            'is_active' => true
        ]);
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#3b82f6'
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'description' => 'Test Transaction',
            'amount' => -100.00,
            'type' => 'expense',
            'transaction_date' => now()
        ]);

        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals($account->id, $transaction->account_id);
        $this->assertEquals($category->id, $transaction->category_id);
        $this->assertEquals('Test Transaction', $transaction->description);
        $this->assertEquals(-100.00, $transaction->amount);
        $this->assertEquals('expense', $transaction->type);
    }

    /** @test */
    public function test_budget_model()
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#3b82f6'
        ]);

        $budget = Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => now(),
            'end_date' => now()->addMonth()
        ]);

        $this->assertEquals($user->id, $budget->user_id);
        $this->assertEquals($category->id, $budget->category_id);
        $this->assertEquals(500.00, $budget->amount);
        $this->assertEquals('monthly', $budget->period);
    }

    /** @test */
    public function test_savings_goal_model()
    {
        $user = User::factory()->create();

        $savingsGoal = SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Test Goal',
            'target_amount' => 1000.00,
            'current_amount' => 0.00,
            'target_date' => now()->addMonths(6),
            'description' => 'Test savings goal'
        ]);

        $this->assertEquals($user->id, $savingsGoal->user_id);
        $this->assertEquals('Test Goal', $savingsGoal->name);
        $this->assertEquals(1000.00, $savingsGoal->target_amount);
        $this->assertEquals(0.00, $savingsGoal->current_amount);
        $this->assertEquals('Test savings goal', $savingsGoal->description);
    }

    /** @test */
    public function test_bill_model()
    {
        $user = User::factory()->create();

        $bill = Bill::create([
            'user_id' => $user->id,
            'name' => 'Test Bill',
            'amount' => 100.00,
            'due_date' => now()->addDays(7),
            'frequency' => 'monthly',
            'is_paid' => false
        ]);

        $this->assertEquals($user->id, $bill->user_id);
        $this->assertEquals('Test Bill', $bill->name);
        $this->assertEquals(100.00, $bill->amount);
        $this->assertEquals('monthly', $bill->frequency);
        $this->assertFalse($bill->is_paid);
    }

    /** @test */
    public function test_model_relationships()
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Account',
            'type' => 'checking',
            'balance' => 1000.00,
            'is_active' => true
        ]);
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#3b82f6'
        ]);

        // Test user relationships
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->accounts);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->categories);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->transactions);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->budgets);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->savingsGoals);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->bills);

        // Test account relationships
        $this->assertEquals($user->id, $account->user->id);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $account->transactions);

        // Test category relationships
        $this->assertEquals($user->id, $category->user->id);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $category->transactions);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $category->budgets);
    }

    /** @test */
    public function test_model_validation()
    {
        $user = User::factory()->create();

        // Test required fields
        $this->expectException('Illuminate\Database\QueryException');
        Account::create([
            'user_id' => $user->id,
            // Missing required fields
        ]);

        $this->expectException('Illuminate\Database\QueryException');
        Category::create([
            'user_id' => $user->id,
            // Missing required fields
        ]);

        $this->expectException('Illuminate\Database\QueryException');
        Transaction::create([
            'user_id' => $user->id,
            // Missing required fields
        ]);
    }

    /** @test */
    public function test_model_scopes()
    {
        $user = User::factory()->create();
        
        // Create active and inactive accounts
        $activeAccount = Account::create([
            'user_id' => $user->id,
            'name' => 'Active Account',
            'type' => 'checking',
            'balance' => 1000.00,
            'is_active' => true
        ]);

        $inactiveAccount = Account::create([
            'user_id' => $user->id,
            'name' => 'Inactive Account',
            'type' => 'checking',
            'balance' => 0.00,
            'is_active' => false
        ]);

        // Test active scope
        $activeAccounts = Account::where('user_id', $user->id)->where('is_active', true)->get();
        $this->assertCount(1, $activeAccounts);
        $this->assertEquals('Active Account', $activeAccounts->first()->name);

        // Test inactive scope
        $inactiveAccounts = Account::where('user_id', $user->id)->where('is_active', false)->get();
        $this->assertCount(1, $inactiveAccounts);
        $this->assertEquals('Inactive Account', $inactiveAccounts->first()->name);
    }

    /** @test */
    public function test_model_mutators_and_accessors()
    {
        $user = User::factory()->create();

        // Test password mutator
        $user->password = 'newpassword';
        $this->assertTrue(Hash::check('newpassword', $user->password));

        // Test email mutator
        $user->email = 'NEWEMAIL@EXAMPLE.COM';
        $this->assertEquals('newemail@example.com', $user->email);
    }

    /** @test */
    public function test_model_soft_deletes()
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Account',
            'type' => 'checking',
            'balance' => 1000.00,
            'is_active' => true
        ]);

        // Test soft delete
        $account->delete();
        $this->assertSoftDeleted('accounts', ['id' => $account->id]);

        // Test restore
        $account->restore();
        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'deleted_at' => null]);
    }

    /** @test */
    public function test_model_events()
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Account',
            'type' => 'checking',
            'balance' => 1000.00,
            'is_active' => true
        ]);

        // Test creating event
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => Category::create([
                'user_id' => $user->id,
                'name' => 'Test Category',
                'type' => 'expense',
                'color' => '#3b82f6'
            ])->id,
            'description' => 'Test Transaction',
            'amount' => -100.00,
            'type' => 'expense',
            'transaction_date' => now()
        ]);

        // Verify account balance was updated
        $account->refresh();
        $this->assertEquals(900.00, $account->balance);
    }

    /** @test */
    public function test_model_factories()
    {
        // Test user factory
        $user = User::factory()->create();
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);

        // Test account factory
        $account = Account::factory()->create();
        $this->assertInstanceOf(Account::class, $account);
        $this->assertNotEmpty($account->name);
        $this->assertNotEmpty($account->type);

        // Test category factory
        $category = Category::factory()->create();
        $this->assertInstanceOf(Category::class, $category);
        $this->assertNotEmpty($category->name);
        $this->assertNotEmpty($category->type);
    }
}
