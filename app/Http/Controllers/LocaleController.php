<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch($locale)
    {
        if (array_key_exists($locale, config('app.available_locales', [
            'en' => 'English',
            'fr' => 'Français',
            'ar' => 'العربية'
        ]))) {
            session()->put('locale', $locale);
            
            if (auth()->check()) {
                auth()->user()->update(['locale' => $locale]);
            }
        }

        return redirect()->back();
    }
}
