<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'reviewer') {
            return $next($request);
        }
        
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}

