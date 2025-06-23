<?php
// app/Http/Middleware/Localization.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language') ?? $request->get('lang', 'en');
        if (!in_array($locale, ['en', 'es'])) {
            $locale = 'en';
        }
        App::setLocale($locale);
        return $next($request);
    }
}
