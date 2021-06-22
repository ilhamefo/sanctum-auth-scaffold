<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HadTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $request->cookie('user_token');

        if ($request->cookie('user_token') !== null) {

            $request->headers->add(['Authorization' => "Bearer $request->cookie('user_token')"]);

            return $next($request);
        } else {
            return response()->json([
                'messages' => 'Unauthenticated'
            ], 401);
        }
    }
}
