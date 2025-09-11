<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user
        $this->adminUser = User::factory()->create([
            'username' => 'admin',
            'full_name' => 'Admin User',
            'pin' => '123456',
            'is_admin' => true,
            'is_approved' => true,
            'approved_at' => now()
        ]);

        // Create a regular user
        $this->regularUser = User::factory()->create([
            'username' => 'regular',
            'full_name' => 'Regular User',
            'pin' => '123456',
            'is_admin' => false,
            'is_approved' => true,
            'approved_at' => now()
        ]);
    }

    /**
     * Test admin dashboard loads for admin user
     */
    public function test_admin_dashboard_loads_for_admin_user()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Admin Dashboard');
    }

    /**
     * Test admin dashboard redirects non-admin users
     */
    public function test_admin_dashboard_redirects_non_admin_users()
    {
        $response = $this->actingAs($this->regularUser)->get('/admin');
        
        $response->assertStatus(403);
    }

    /**
     * Test admin dashboard redirects unauthenticated users
     */
    public function test_admin_dashboard_redirects_unauthenticated_users()
    {
        $response = $this->get('/admin');
        
        $response->assertRedirect('/login');
    }

    /**
     * Test admin users page loads for admin user
     */
    public function test_admin_users_page_loads_for_admin_user()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/users');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.users');
        $response->assertSee('User Management');
    }

    /**
     * Test admin pending approvals page loads for admin user
     */
    public function test_admin_pending_approvals_page_loads_for_admin_user()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/pending-approvals');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.pending-approvals');
        $response->assertSee('Pending Approvals');
    }

    /**
     * Test admin can approve users
     */
    public function test_admin_can_approve_users()
    {
        $pendingUser = User::factory()->create([
            'username' => 'pending',
            'full_name' => 'Pending User',
            'pin' => '123456',
            'is_approved' => false,
            'approved_at' => null
        ]);

        // Create an approval request for the user
        \App\Models\UserApprovalRequest::create([
            'user_id' => $pendingUser->id,
            'status' => 'pending',
            'message' => 'Please approve my account'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.approve-user', $pendingUser));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'is_approved' => true
        ]);
    }

    /**
     * Test admin can reject users
     */
    public function test_admin_can_reject_users()
    {
        $pendingUser = User::factory()->create([
            'username' => 'pending',
            'full_name' => 'Pending User',
            'pin' => '123456',
            'is_approved' => false,
            'approved_at' => null
        ]);

        // Create an approval request for the user
        \App\Models\UserApprovalRequest::create([
            'user_id' => $pendingUser->id,
            'status' => 'pending',
            'message' => 'Please approve my account'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.reject-user', $pendingUser));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'is_approved' => false
        ]);
    }

    /**
     * Test non-admin users cannot access admin routes
     */
    public function test_non_admin_users_cannot_access_admin_routes()
    {
        $adminRoutes = [
            '/admin',
            '/admin/users',
            '/admin/pending-approvals'
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->actingAs($this->regularUser)->get($route);
            $response->assertStatus(403);
        }
    }

    /**
     * Test admin pages have proper navigation elements
     */
    public function test_admin_pages_have_proper_navigation()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin');
        
        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Manage Users');
        $response->assertSee('Pending Approvals');
    }

    /**
     * Test admin dashboard shows user statistics
     */
    public function test_admin_dashboard_shows_user_statistics()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin');
        
        $response->assertStatus(200);
        $response->assertSee('Total Users');
        $response->assertSee('Pending Approvals');
        $response->assertSee('Approved Users');
    }
}
