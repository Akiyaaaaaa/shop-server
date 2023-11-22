<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Execute the next middleware in the pipeline
        $response = $next($request);

        // Add custom headers to the response
        $response->header('X-App-Response', 'TestFSD');

        // Return the modified response
        return $response;
    }
}
