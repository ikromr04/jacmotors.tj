<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! array_key_exists($locale, config('app.locales'))) {
            abort(404);
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        // Controllers don't need the prefix as an argument.
        $request->route()->forgetParameter('locale');

        return $next($request);
    }
}
