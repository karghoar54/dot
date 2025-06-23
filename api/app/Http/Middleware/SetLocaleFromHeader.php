<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next)
    {
        $lang = $request->header('Accept-Language');
        if ($lang && in_array($lang, ['en', 'es'])) {
            app()->setLocale($lang);
        }
        return $next($request);
    }
}
