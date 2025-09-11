<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserApprovalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show landing page
     */
    public function landing()
    {
        return view('auth.landing');
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || $user->pin !== $request->pin) {
            return redirect()->back()
                ->withErrors(['username' => 'Invalid credentials'])
                ->withInput();
        }

        if (!$user->is_approved) {
            return redirect()->route('pending-approval');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show signup form
     */
    public function showSignup()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.signup');
    }

    /**
     * Handle signup
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username|min:3|max:50',
            'full_name' => 'required|string|min:2|max:255',
            'pin' => 'required|string|size:6',
            'pin_confirmation' => 'required|same:pin',
            'terms' => 'required|accepted',
            'privacy' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'pin' => $request->pin,
            'currency' => 'PHP',
            'timezone' => 'Asia/Manila',
            'is_admin' => false,
            'is_approved' => false,
        ]);

        // Create approval request
        UserApprovalRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return redirect()->route('pending-approval');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    /**
     * Show pending approval page
     */
    public function pendingApproval()
    {
        return view('auth.pending-approval');
    }

    /**
     * Show account rejected page
     */
    public function accountRejected()
    {
        return view('auth.account-rejected');
    }

    /**
     * Show account approved page
     */
    public function accountApproved()
    {
        return view('auth.account-approved');
    }
}
