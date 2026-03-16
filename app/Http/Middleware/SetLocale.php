<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if (auth()->check() && auth()->user()->locale) {
            $locale = auth()->user()->locale;
        }

        if (array_key_exists($locale, config('app.available_locales', ['en' => 'English', 'fr' => 'Français', 'ar' => 'العربية']))) {
            app()->setLocale($locale);
        } else {
            app()->setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
