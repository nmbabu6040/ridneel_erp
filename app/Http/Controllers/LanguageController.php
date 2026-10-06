<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['bn', 'en'], true), 404);

        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->update(['preferred_locale' => $locale]);
        }

        return back();
    }
}
