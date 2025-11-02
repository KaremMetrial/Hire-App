<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottleRequests;

class ThrottleRequests extends BaseThrottleRequests
{
    /**
     * Create a 'too many attempts' response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $maxAttempts
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildResponse($request, $maxAttempts)
    {
        $response = parent::buildResponse($request, $maxAttempts);

        // Add custom headers for rate limiting
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', 0);
        $response->headers->set('Retry-After', $this->limiter->availableIn($request->fingerprint()));

        return $response;
    }
}
