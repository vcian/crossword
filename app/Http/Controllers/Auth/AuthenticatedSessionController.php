<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Check if user is admin
        if (Auth::user()->is_admin) {
            return redirect()->route('leaderboard');
        }

        // Check if user has completed a puzzle recently (within last 24 hours)
        $latestScore = UserScore::where('user_id', Auth::id())
            ->where('completed', true)
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->first();

        if ($latestScore) {
            // Redirect to completion page with the puzzle
            return redirect()->route('puzzles.completion', $latestScore->puzzle);
        }

        // If no recent completion, get a random active puzzle
        $randomPuzzle = Puzzle::where('is_active', true)->inRandomOrder()->first();
        return $randomPuzzle 
            ? redirect()->route('puzzles.play', $randomPuzzle)
            : redirect()->route('home');
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
