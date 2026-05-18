<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->two_factor_enabled && !$request->session()->get('two_factor_verified')) {
            if (!$request->routeIs('two-factor.verify') && !$request->routeIs('two-factor.verify.post')) {
                return redirect()->route('two-factor.verify');
            }
        }

        return $next($request);
    }
}
