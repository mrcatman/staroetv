<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Closure;

class SetUserLastSeenPage
{
    public function handle($request, Closure $next)
    {
//        if (!$request->secure()) {
//            $uri = $request->getRequestUri();
//           // return redirect()->secure($uri);
//        }
        if ($request->method() === "GET") {
            if ($user = auth()->user()) {
                $user->ip_address = request()->header('x-real-ip', request()->ip());
                $user->was_online = Carbon::now();
                $user->last_page_seen = $request->path();
                $user->save();
            }
        }
        return $next($request);
    }
}
