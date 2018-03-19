<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\WxUser;
use Exception;

class CheckToken
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
        $token = $request->header('token');
        try {
            $user = WxUser::where('token', $token)->firstOrFail();
        } catch (Exception $err) {
            return response('', 403);
        }
        return $next($request);
    }
}
