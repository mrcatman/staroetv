<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionsHelper;
use Closure;

class checkAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (PermissionsHelper::allows('admbar')) {
            return $next($request);
        } else {
            if ($request->ajax()){
                return response()->json(['status' => 0, 'text' => 'Ошибка доступа']);
            }
            return redirect("https://staroetv.su/");
        }
    }
}
