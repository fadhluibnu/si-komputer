<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * Handle the login request.
     */
    public function authenticate(Request $request)
    {
        // Logic for authentication
        $credentials = $request->validate([

            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Show the registration form.
     */
    public function register()
    {
        return view('auth.register');
    }

    /**
     * Handle the registration request.
     */
    public function store(Request $request)
    {
        // Logic for storing user data
    }

    /**
     * Handle the logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
