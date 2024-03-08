<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Closure;

class setLastPage
{
    public function handle($request, Closure $next)
    {

        if (!$request->secure()) {
            $uri = $request->getRequestUri();
           // return redirect()->secure($uri);
        }


        $is_admin = PermissionsHelper::allows('admbar');
        if (!$is_admin && request()->path() !== "admin-login" && !($request->method() === "POST" && request()->path() == "login")) {
            if (!request()->has('test')) {
               // return redirect("https://staroetv.su/admin-login");
            }
        }
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
