<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        // Also allow login with email
        if (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
            $credentials = [
                'email' => $request->username,
                'password' => $request->password,
            ];
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Check if user is admin
            if (Auth::user()->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Non-admin users - logout and redirect back
            Auth::logout();
            return back()->withErrors([
                'username' => 'Access denied. Admin privileges required.',
            ]);
        }

        throw ValidationException::withMessages([
            'username' => ['The provided credentials do not match our records.'],
        ]);
    }
}

