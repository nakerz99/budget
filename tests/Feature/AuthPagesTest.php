<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test landing page loads correctly
     */
    public function test_landing_page_loads()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.landing');
        $response->assertSee('Take Control of Your Finances');
    }

    /**
     * Test login page loads correctly
     */
    public function test_login_page_loads()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Welcome Back');
        $response->assertSee('Username');
        $response->assertSee('6-Digit PIN');
    }

    /**
     * Test signup page loads correctly
     */
    public function test_signup_page_loads()
    {
        $response = $this->get('/signup');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.signup');
        $response->assertSee('Join Us Today');
        $response->assertSee('Full Name');
        $response->assertSee('Username');
        $response->assertSee('6-Digit PIN');
    }

    /**
     * Test pending approval page loads correctly
     */
    public function test_pending_approval_page_loads()
    {
        $response = $this->get('/pending-approval');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.pending-approval');
        $response->assertSee('Pending Approval');
    }

    /**
     * Test account rejected page loads correctly
     */
    public function test_account_rejected_page_loads()
    {
        $response = $this->get('/account-rejected');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.account-rejected');
        $response->assertSee('Account Rejected');
    }

    /**
     * Test account approved page loads correctly
     */
    public function test_account_approved_page_loads()
    {
        $response = $this->get('/account-approved');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.account-approved');
        $response->assertSee('Account Approved');
    }

    /**
     * Test login form validation
     */
    public function test_login_form_validation()
    {
        $response = $this->post('/login', []);
        
        $response->assertSessionHasErrors(['username', 'pin']);
    }

    /**
     * Test signup form validation
     */
    public function test_signup_form_validation()
    {
        $response = $this->post('/signup', []);
        
        $response->assertSessionHasErrors(['full_name', 'username', 'pin']);
    }

    /**
     * Test successful user signup
     */
    public function test_successful_user_signup()
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
        
        $response->assertRedirect('/pending-approval');
        $this->assertDatabaseHas('users', [
            'username' => 'johndoe',
            'full_name' => 'John Doe',
            'is_approved' => false
        ]);
    }

    /**
     * Test logout redirects to landing page
     */
    public function test_logout_redirects_to_landing()
    {
        $response = $this->get('/logout');
        
        $response->assertRedirect('/');
    }
}
