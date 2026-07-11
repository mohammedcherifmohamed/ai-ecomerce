<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthWebController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $result = $this->authService->login($request->only('email', 'password'));
            auth()->login($result['user']);

            $user = $result['user'];
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->isEmployee()) {
                return redirect()->route('employee.orders.index');
            }

            return redirect()->route('home');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['email' => $e->getMessage()])->onlyInput('email');
        }
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $result = $this->authService->register(
            $request->only('name', 'email', 'password', 'phone', 'address', 'city', 'state', 'zip_code', 'country')
        );

        auth()->login($result['user']);

        return redirect()->route('home')->with('success', 'Welcome! Your account has been created.');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
