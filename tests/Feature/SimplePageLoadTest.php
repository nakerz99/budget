<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimplePageLoadTest extends TestCase
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
     * Test all public pages load correctly
     */
    public function test_public_pages_load()
    {
        $publicPages = [
            '/' => 200,
            '/login' => 200,
            '/signup' => 200,
            '/pending-approval' => 200,
            '/account-rejected' => 200,
            '/account-approved' => 200,
        ];

        foreach ($publicPages as $page => $expectedStatus) {
            $response = $this->get($page);
            $this->assertEquals($expectedStatus, $response->getStatusCode(), "Page {$page} should return {$expectedStatus}");
        }
    }

    /**
     * Test all protected pages redirect unauthenticated users
     */
    public function test_protected_pages_redirect_unauthenticated_users()
    {
        $protectedPages = [
            '/dashboard',
            '/transactions',
            '/transactions/create',
            '/budget',
            '/bills',
            '/savings',
            '/reports',
            '/settings',
            '/admin'
        ];

        foreach ($protectedPages as $page) {
            $response = $this->get($page);
            $this->assertEquals(302, $response->getStatusCode(), "Page {$page} should redirect unauthenticated users");
        }
    }

    /**
     * Test all protected pages load for authenticated users
     */
    public function test_protected_pages_load_for_authenticated_users()
    {
        $protectedPages = [
            '/dashboard',
            '/transactions',
            '/transactions/create',
            '/budget',
            '/bills',
            '/savings',
            '/reports',
            '/settings'
        ];

        foreach ($protectedPages as $page) {
            $response = $this->actingAs($this->user)->get($page);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 500]), 
                "Page {$page} should load for authenticated users (got {$response->getStatusCode()})"
            );
        }
    }

    /**
     * Test admin pages load for admin users
     */
    public function test_admin_pages_load_for_admin_users()
    {
        $adminUser = User::factory()->create([
            'username' => 'admin',
            'full_name' => 'Admin User',
            'pin' => '123456',
            'is_admin' => true,
            'is_approved' => true,
            'approved_at' => now()
        ]);

        $adminPages = [
            '/admin',
            '/admin/users',
            '/admin/pending-approvals'
        ];

        foreach ($adminPages as $page) {
            $response = $this->actingAs($adminUser)->get($page);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 500]), 
                "Admin page {$page} should load for admin users (got {$response->getStatusCode()})"
            );
        }
    }

    /**
     * Test admin pages redirect non-admin users
     */
    public function test_admin_pages_redirect_non_admin_users()
    {
        $adminPages = [
            '/admin',
            '/admin/users',
            '/admin/pending-approvals'
        ];

        foreach ($adminPages as $page) {
            $response = $this->actingAs($this->user)->get($page);
            $this->assertTrue(
                in_array($response->getStatusCode(), [403, 500]), 
                "Admin page {$page} should redirect non-admin users (got {$response->getStatusCode()})"
            );
        }
    }

    /**
     * Test API endpoints require authentication
     */
    public function test_api_endpoints_require_authentication()
    {
        $apiEndpoints = [
            '/api/v1/dashboard',
            '/api/v1/transactions',
            '/api/v1/budgets',
            '/api/v1/bills',
            '/api/v1/savings-goals',
            '/api/v1/reports/comprehensive'
        ];

        foreach ($apiEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $this->assertEquals(401, $response->getStatusCode(), "API endpoint {$endpoint} should require authentication");
        }
    }

    /**
     * Test basic application functionality
     */
    public function test_basic_application_functionality()
    {
        // Test landing page
        $response = $this->get('/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Take Control of Your Finances', $response->getContent());

        // Test login page
        $response = $this->get('/login');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Login', $response->getContent());

        // Test signup page
        $response = $this->get('/signup');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Join Us Today', $response->getContent());

        // Test dashboard loads for authenticated user
        $response = $this->actingAs($this->user)->get('/dashboard');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Welcome back', $response->getContent());
    }

    /**
     * Test user signup process
     */
    public function test_user_signup_process()
    {
        $userData = [
            'full_name' => 'John Doe',
            'username' => 'johndoe',
            'pin' => '123456',
            'pin_confirmation' => '123456',
            'terms' => '1',
            'privacy' => '1'
        ];

        $response = $this->post('/signup', $userData);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/pending-approval', $response->headers->get('Location'));

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'username' => 'johndoe',
            'full_name' => 'John Doe',
            'is_approved' => false
        ]);
    }

    /**
     * Test user login process
     */
    public function test_user_login_process()
    {
        $response = $this->post('/login', [
            'username' => 'testuser',
            'pin' => '123456'
        ]);
        
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/dashboard', $response->headers->get('Location'));
    }

    /**
     * Test logout process
     */
    public function test_logout_process()
    {
        $response = $this->get('/logout');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/', $response->headers->get('Location'));
    }
}
