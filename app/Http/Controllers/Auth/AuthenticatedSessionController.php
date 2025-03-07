<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Puzzle;
use App\Models\UserScore;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
            ]);

            $user = \App\Models\User::where('email', $request->email)->first();
            Auth::login($user);

            // Check if user is admin
            if ($user->is_admin) {
                return redirect()->route('leaderboard');
            }

            // Check if user has completed a puzzle recently
            $latestScore = UserScore::where('user_id', $user->id)
                ->where('completed', true)
                ->where('created_at', '>=', now()->subHours(24))
                ->latest()
                ->first();

            if ($latestScore) {
                return redirect()->route('puzzles.completion', $latestScore->puzzle);
            }

            // Get random active puzzle
            $randomPuzzle = Puzzle::where('is_active', true)->inRandomOrder()->first();
            return $randomPuzzle 
                ? redirect()->route('puzzles.play', $randomPuzzle)
                : redirect()->route('home');
        } catch (\Exception $e) {
            \Log::error('Login Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Login failed. Please try again.']);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
