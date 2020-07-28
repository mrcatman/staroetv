<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Closure;

class setLastPage
{
    public function handle($request, Closure $next)
    {
        if ($request->method() === "GET") {
            if ($user = auth()->user()) {
                $user->was_online = Carbon::now();
                $user->last_page_seen = $request->path();
                $user->save();
            }
        }
        return $next($request);
    }
}
