<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class HandlePayingUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->subscribed(env('STRIPE_SUBSCRIPTION_PRO_NAME'))) {
            return redirect()->route('stripe.checkout');
        }

        return $next($request);
    }
}


