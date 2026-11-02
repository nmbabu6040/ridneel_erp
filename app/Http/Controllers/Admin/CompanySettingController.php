<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.company', [
            'settings' => CompanySetting::current(),
        ]);
    }


    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                => ['required', 'string', 'max:150'],
            'name_bn'             => ['nullable', 'string', 'max:150'],
            'tagline'             => ['nullable', 'string', 'max:150'],
            'address'             => ['nullable', 'string', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:30'],
            'email'               => ['nullable', 'email', 'max:150'],
            'website'             => ['nullable', 'string', 'max:150'],
            'currency_symbol'     => ['required', 'string', 'max:10'],
            'currency_code'       => ['required', 'string', 'max:10'],
            'invoice_footer_note' => ['nullable', 'string', 'max:500'],

            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],

            'favicon_emoji' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp,ico',
                'max:512',
            ],
        ]);

        $settings = CompanySetting::current();

        $oldLogoPath = $settings->logo_path;
        $oldFaviconPath = $settings->favicon_emoji;

        // Upload company logo
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')
                ->store('company', 'public');
        }

        // Upload favicon image
        if ($request->hasFile('favicon_emoji')) {
            $data['favicon_emoji'] = $request->file('favicon_emoji')
                ->store('company', 'public');
        }

        // Remove uploaded file objects from update data
        unset($data['logo']);

        // Update database
        $settings->update($data);

        // Clear cached settings
        CompanySetting::forget();

        // Delete old files only after successful database update
        if ($request->hasFile('logo') && $oldLogoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        if ($request->hasFile('favicon_emoji') && $oldFaviconPath) {
            Storage::disk('public')->delete($oldFaviconPath);
        }

        return back()->with('success', __('app.settings_saved'));
    }
}
