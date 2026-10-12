<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Each language has its own copy of the routes, registered with the language as a middleware parameter.
     */
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
