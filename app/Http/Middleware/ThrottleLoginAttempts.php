<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ThrottleRequests;
use Closure;

class ThrottleLoginAttempts extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = 5, $decayMinutes = 1)
    {
        return parent::handle($request, $next, $maxAttempts, $decayMinutes);
    }
} 