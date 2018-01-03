<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class DatabaseTransaction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \Exception|\Throwable
     */
    public function handle($request, Closure $next)
    {
        if (in_array(strtolower($request->method()), ['post', 'put', 'patch', 'delete'])) {
            return DB::transaction(function () use ($request, $next) {
                return $next($request);
            });
        } else {
            return $next($request);
        }
    }
}
