<?php

namespace BWTV\ForTheAPIs\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * usually put it to the first of 'api' middleware-group
 */
class APIHandling
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
        /**
         * 使用 scoped 是需要在每個 lifecycle 釋放，而不是 singleton 一直存在
         */
        app()->scoped(\Illuminate\Contracts\Debug\ExceptionHandler::class, function ($app) {
            return $app->get(\BWTV\ForTheAPIs\Exceptions\APIExceptionHandler::class);
        });

        /**
         * Laravel 12+ 後有較嚴謹的檢查
         */
        if ($request->header('Accept') === '*/*') {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
