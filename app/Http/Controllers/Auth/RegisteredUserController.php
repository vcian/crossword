<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Puzzle;
use App\Models\EmailOtp;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Get a random active puzzle
        $randomPuzzle = Puzzle::where('is_active', true)->inRandomOrder()->first();

        // Redirect to the random puzzle if exists, otherwise to home
        return $randomPuzzle 
            ? redirect()->route('puzzles.play', $randomPuzzle) 
            : redirect()->route('home');
    }

    /**
     * Send OTP for registration.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in session for verification
        $request->session()->put('registration_data', [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10)
        ]);

        // Send OTP email
        try {
            \Log::info('Attempting to send OTP email', [
                'to' => $request->email,
                'mail_config' => [
                    'host' => config('mail.host'),
                    'port' => config('mail.port'),
                    'encryption' => config('mail.encryption'),
                    'from_address' => config('mail.from.address'),
                ]
            ]);

            // Test mail configuration
            // if (!$this->testMailConnection()) {
            //     \Log::error('Mail configuration test failed');
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Mail server connection failed. Please contact support.'
            //     ], 500);
            // }

            Mail::send('emails.otp', ['otp' => $otp], function($message) use ($request) {
                $message->to($request->email)
                        ->subject('Your Verification Code for Crossword Registration');
            });

            \Log::info('OTP email sent successfully');

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email.'
            ]);
        } catch (\Swift_TransportException $e) {
            \Log::error('Mail transport error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Email service is temporarily unavailable. Please try again later.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.'
            ], 500);
        }
    }

    /**
     * Test mail connection before sending
     */
    private function testMailConnection()
    {
        try {
            $transport = Mail::getSymfonyTransport();
            if (method_exists($transport, 'start')) {
                $transport->start();
            }
            return true;
        } catch (\Exception $e) {
            \Log::error('Mail connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resend OTP for registration.
     */
    public function resendOtp(Request $request)
    {
        $registrationData = $request->session()->get('registration_data');
        
        if (!$registrationData) {
            return response()->json([
                'success' => false,
                'message' => 'Registration session expired. Please start over.'
            ], 400);
        }

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update session data
        $registrationData['otp'] = $otp;
        $registrationData['expires_at'] = now()->addMinutes(10);
        $request->session()->put('registration_data', $registrationData);

        // Send new OTP email
        try {
            Mail::send('emails.otp', ['otp' => $otp], function($message) use ($registrationData) {
                $message->to($registrationData['email'])
                        ->subject('Your New Verification Code for Crossword Registration');
            });

            return response()->json([
                'success' => true,
                'message' => 'New verification code sent to your email.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP and complete registration.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $registrationData = $request->session()->get('registration_data');
        
        if (!$registrationData) {
            return response()->json([
                'success' => false,
                'message' => 'Registration session expired. Please start over.'
            ], 400);
        }

        if (now()->isAfter($registrationData['expires_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code expired. Please request a new one.'
            ], 400);
        }

        if ($request->otp !== $registrationData['otp']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.'
            ], 400);
        }

        try {
            $user = User::create([
                'name' => $registrationData['name'],
                'email' => $registrationData['email'],
                'phone' => $registrationData['phone'],
                'company_name' => $registrationData['company_name'],
                'password' => Str::random(32), // Generate a random password as it's not used
            ]);

            event(new Registered($user));

            Auth::login($user);
            
            $request->session()->forget('registration_data');

            // Get a random active puzzle
            $randomPuzzle = Puzzle::where('is_active', true)->inRandomOrder()->first();

            return response()->json([
                'success' => true,
                'redirect' => $randomPuzzle 
                    ? route('puzzles.play', $randomPuzzle)
                    : route('home')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete registration. Please try again.'
            ], 500);
        }
    }
}
