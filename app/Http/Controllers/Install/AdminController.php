<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Admin account creation controller
 */
class AdminController extends Controller
{
    /**
     * Display admin account creation page
     */
    public function index(): View
    {
        return view('install.admin');
    }

    /**
     * Create admin user account
     */
    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);
            $user->forceFill(['role' => 'admin', 'is_active' => true, 'is_verified' => true])->save();

            return redirect()->route('install.email');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['email' => 'Failed to create admin user: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
