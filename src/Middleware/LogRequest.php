<?php

namespace Firevel\RequestLogger\Middleware;

use Closure;
use Firevel\RequestLogger\Services\QueryLogger;
use Illuminate\Http\Request;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('request-logger.disabled')) {
            return $next($request);
        }
        // Execute only inside App Engine.
        if (env('GAE_SERVICE')) {
            // Dispatch log job after response is sent.
            if (config('request-logger.debug')) {
                \Firevel\RequestLogger\Jobs\LogRequest::dispatchNow($request);
            } else {
                \Firevel\RequestLogger\Jobs\LogRequest::dispatchAfterResponse($request);
            }
        }

        return $next($request);
    }
}
