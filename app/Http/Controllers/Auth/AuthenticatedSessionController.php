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
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginOtpMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
                'otp' => ['required', 'string', 'size:6'],
            ]);

            $cacheKey = 'login_otp_' . $request->email;
            $storedOtp = Cache::get($cacheKey);

            if (!$storedOtp || $storedOtp !== $request->otp) {
                return back()->withErrors(['otp' => 'Invalid OTP']);
            }

            $user = \App\Models\User::where('email', $request->email)->first();
            Auth::login($user);
            
            // Clear the OTP after successful login
            Cache::forget($cacheKey);

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

    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
            ], [
                'email.exists' => 'This email is not registered in our system.'
            ]);

            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $cacheKey = 'login_otp_' . $request->email;
            
            // Store OTP in cache for 5 minutes
            Cache::put($cacheKey, $otp, 300);
            
            // Send OTP email
            Mail::to($request->email)->send(new LoginOtpMail($otp));
            
            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('OTP Send Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e instanceof \Illuminate\Validation\ValidationException 
                    ? $e->errors()['email'][0] 
                    : 'Failed to send OTP. Please try again.'
            ], $e instanceof \Illuminate\Validation\ValidationException ? 422 : 500);
        }
    }
}
