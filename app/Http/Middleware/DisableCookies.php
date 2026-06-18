<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableCookies
{
    public function handle(Request $request, Closure $next): Response
    {
        config(['session.driver' => 'array']);
        return $next($request);
    }
}
